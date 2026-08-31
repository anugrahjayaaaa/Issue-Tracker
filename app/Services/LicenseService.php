<?php

namespace App\Services;

use App\Models\License;
use App\Models\Plan;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Issues + verifies signed license keys (Model 1: per-instance).
 *
 * One method, two callers (doc §3b):
 *  - A. auto: PG webhook success -> issue(recurring, expires_at)
 *  - B. manual: admin console/UI -> issue(lifetime|manual, expires_at=null)
 *
 * License is NON-TRANSFERABLE (doc §9b): issued_to is bound to the instance.
 */
final class LicenseService
{
    /** Build the signed key string. */
    private static function sign(string $slug, ?string $expiresAt): string
    {
        $secret = config('app.license_secret');

        // ponytail: fail closed only in prod/staging — an unset/placeholder secret makes
        // every key forgeable. Local/test run dummy mode and sign with a dev key so the
        // template works out-of-the-box.
        if (empty($secret) || $secret === 'change-me-in-production') {
            if (app()->environment(['production', 'staging'])) {
                throw new \RuntimeException('app.license_secret is not set — cannot sign license keys.');
            }
            $secret = 'dev-license-secret';
        }

        $Payload = $slug.'|'.($expiresAt ?? 'lifetime');
        $hash = substr(sha1($Payload.$secret), 0, 12);

        return 'LIC-'.Str::upper($slug).'-'.Str::upper($hash);
    }

    /**
     * Issue a license. Returns the signed key.
     */
    public static function issue(string $planSlug, array $attrs = []): string
    {
        $plan = Plan::where('slug', $planSlug)->firstOrFail();
        $expiresAt = $attrs['expires_at'] ?? null;
        $type = $attrs['type'] ?? ($expiresAt ? 'recurring' : 'manual');

        $key = self::sign($planSlug, $expiresAt);

        // ponytail: snapshot plan limits/features at issue time (catalog versioning, §10.8)
        License::create([
            'plan_slug' => $planSlug,
            'user_id' => $attrs['user_id'] ?? null,
            'license_key' => $key,
            'type' => $type,
            'status' => 'active',
            'issued_to' => $attrs['issued_to'] ?? null,
            'expires_at' => $expiresAt,
            'snapshot' => ['limits' => $plan->limits, 'features' => $plan->features],
        ]);

        return $key;
    }

    /**
     * Activate a license on this instance. Verifies signature + row, then
     * writes settings atomically. Race-safe (doc §10.9): only one active
     * license per instance; client cannot flip settings.active_plan alone.
     */
    public static function activate(string $key, ?string $issuedTo = null): bool
    {
        $license = License::where('license_key', $key)->first();

        if (! $license || ! self::verify($key, $license)) {
            return false;
        }
        if (! $license->isActiveAndValid()) {
            return false;
        }

        // ponytail: single active license per instance — revoke any other active one
        License::where('status', 'active')
            ->where('id', '!=', $license->id)
            ->update(['status' => 'expired']);

        $license->update(['issued_to' => $issuedTo, 'status' => 'active']);

        Setting::set('active_plan', $license->plan_slug);
        Setting::set('license_key', $key);

        activity()->withProperties(['plan' => $license->plan_slug])
            ->log('license.activated');

        return true;
    }

    /** Verify the key's signature against the stored license. */
    public static function verify(string $key, License $license): bool
    {
        $expected = self::sign($license->plan_slug, $license->expires_at?->format('Y-m-d H:i:s'));

        return hash_equals($expected, $key);
    }

    /** Status of the currently activated license (or 'none'). */
    public static function status(): string
    {
        $license = self::activeLicense();
        if (! $license) {
            return 'none';
        }
        if ($license->status === 'revoked') {
            return 'revoked';
        }
        if ($license->expires_at && $license->expires_at->isPast()) {
            return 'expired';
        }
        return 'active';
    }

    /** Days left on the activated license (null = lifetime/INF). */
    public static function daysLeft(): ?int
    {
        $license = self::activeLicense();
        if (! $license || ! $license->expires_at) {
            return null;
        }
        return (int) now()->diffInDays($license->expires_at, false);
    }

    /**
     * Shared lookup of the currently activated license row (if any).
     * Reads the key from settings once; used by status(), daysLeft(),
     * and DashboardController to avoid duplicate settings+license queries.
     */
    private static function activeLicense(): ?License
    {
        $key = Setting::get('license_key');
        if (! $key) {
            return null;
        }
        return License::where('license_key', $key)->first();
    }

    /** Revoke a license (abuse / manual). Instant lock (doc §10.7). */
    public static function revoke(string $key, ?string $reason = null): void
    {
        $license = License::where('license_key', $key)->first();
        if (! $license) {
            return;
        }
        $license->update(['status' => 'revoked', 'revoke_reason' => $reason]);
        if (Setting::get('license_key') === $key) {
            Setting::set('active_plan', 'free');
            Setting::set('license_key', null);
        }
        activity()->withProperties(['plan' => $license->plan_slug, 'reason' => $reason])
            ->log('license.revoked');
    }
}
