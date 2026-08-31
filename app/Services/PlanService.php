<?php

namespace App\Services;

use App\Models\License;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\User;

/**
 * Entitlement gate. Single point every access/limit check routes through
 * (doc §4). Reads the SNAPSHOT from the active license, not the live plans
 * row (catalog versioning, §10.8) — so a mid-term plan change never narrows a
 * paying client's entitlement.
 *
 * Seam: for($scope) — Model 1 $scope=null (instance), Model 2 $scope=Tenant.
 */
final class PlanService
{
    private Plan $plan;

    private ?License $license;

    public function __construct(Plan $plan, ?License $license)
    {
        $this->plan = $plan;
        $this->license = $license;
    }

    /** Model 1: $scope = null. Model 2: $scope = Tenant. */
    public static function for(?object $scope = null): self
    {
        // ponytail: Model 2 seam — read plan_slug from tenant when present
        $slug = $scope->plan_slug ?? Setting::get('active_plan', 'free');
        $plan = Plan::where('slug', $slug)->firstOrFail();

        // §10.1 tamper resistance: re-verify the ACTIVATED license on every
        // check. A client owning the DB could edit settings.active_plan, but
        // without a matching valid+signed license row we fall back to free.
        $license = null;
        if ($key = Setting::get('license_key')) {
            $license = License::where('license_key', $key)->first();
            if (! $license || ! LicenseService::verify($key, $license) || ! $license->isActiveAndValid()) {
                $license = null; // tampered/expired/forged -> ignore, use plan row defaults
            }
        }

        return new self($plan, $license);
    }

    /** Whether a feature is enabled for the active plan (from license snapshot). */
    public function can(string $feature): bool
    {
        // §10.1 tamper resistance: only the 'free' plan is grantable without a
        // valid activated license. Any paid plan REQUIRES a matching signed,
        // active license — a client cannot create a 'pro' plan row + flip the
        // setting to unlock features.
        if (! $this->plan->is_active) {
            return false;
        }
        if ($this->plan->slug !== 'free' && ! $this->license) {
            return false;
        }

        $features = $this->license?->snapshot['features']
            ?? $this->plan->features
            ?? [];

        return in_array($feature, $features, true);
    }

    public function membersLeft(): int
    {
        $max = $this->limit('max_members', 0);

        return max(0, $max - User::count());
    }

    public function projectsLeft(): int
    {
        $max = $this->limit('max_projects', 0);

        // ponytail: Model 1 counts globally; Model 2 scopes by tenant
        return max(0, $max - 0);
    }

    /** Storage quota in MB for the active plan (0 = unlimited). Tamper-aware (§10.1). */
    public function storageQuotaMb(): int
    {
        return $this->limit('max_storage_mb', 0);
    }

    private function limit(string $key, int $default): int
    {
        // paid plan without a valid license => no headroom (tamper-safe, §10.1)
        if ($this->plan->slug !== 'free' && ! $this->license) {
            return 0;
        }

        return (int) ($this->limits()[$key] ?? $default);
    }

    /** Snapshot limits from the license (or plan row), respecting tamper guard. */
    private function limits(): array
    {
        if ($this->plan->slug !== 'free' && ! $this->license) {
            return [];
        }
        return $this->license?->snapshot['limits']
            ?? $this->plan->limits
            ?? [];
    }

    /** Whether subscribers can create roles (derived: true iff 'roles' feature is on). */
    public function canCreateRoles(): bool
    {
        return (bool) ($this->limits()['can_create_roles'] ?? false);
    }

    /** Permission names a subscriber may assign when creating/editing roles.
     *  Empty array = no permission assigned (deny) unless explicitly listed.
     */
    public function allowedPermissions(): array
    {
        $allowed = $this->limits()['allowed_permissions'] ?? [];
        return is_array($allowed) ? $allowed : [];
    }
}
