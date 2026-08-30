<?php

namespace App\Providers;

use App\Http\Middleware\PeriscopeAuthorize;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Pennant\Feature;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use TortoiseIT\LaravelPeriscope\Http\Middleware\Authorize;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        // ponytail: hard-disable storage outside local — Telescope persists PII
        // (emails, tokens) in DB; keep it local-only in a base project.
        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            return $isLocal;
        });

        // ponytail: Periscope hardwires Telescope::check() (the viewTelescope gate)
        // in its own Authorize middleware. Swap that binding so Periscope uses our
        // independent viewPeriscope gate (separate role/permission from Telescope).
        if (class_exists(Authorize::class)) {
            $this->app->bind(
                Authorize::class,
                fn () => new PeriscopeAuthorize
            );
        }
    }

    /**
     * Override parent: make the 'telescope' feature flag authoritative in ALL
     * environments (parent short-circuits with environment('local') and bypasses
     * the gate, so a disabled feature would still be reachable on local).
     */
    protected function authorization(): void
    {
        $this->gate();

        Telescope::auth(function ($request) {
            return Gate::check('viewTelescope', [$request->user()]);
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function (?User $user) {
            // ponytail: typed User throws on null (stateless Telescope API calls
            // have no resolved user) -> Gate silently denies. Resolve from auth().
            $user ??= auth()->user();

            if (! $user instanceof User) {
                return false;
            }

            // ponytail: require BOTH the telescope.view permission AND the
            // enabled 'telescope' feature flag in every environment, so even
            // super-admin is denied unless explicitly granted + enabled.
            return $user->can('telescope.view') && Feature::active('telescope');
        });

        // ponytail: independent gate for Periscope (separate permission + feature
        // flag from Telescope) so a role can reach Periscope without Telescope.
        Gate::define('viewPeriscope', function (?User $user) {
            $user ??= auth()->user();

            if (! $user instanceof User) {
                return false;
            }

            return $user->can('periscope.view') && Feature::active('periscope');
        });
    }
}
