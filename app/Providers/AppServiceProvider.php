<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Observers\PermissionObserver;
use App\Observers\RoleObserver;
use App\Observers\UserObserver;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use Sentry\Sentry;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ponytail: module flags are GLOBAL (not per-user); force a fixed scope so
        // Feature::active/deactivate behave app-wide, like the old DB table.
        Feature::resolveScopeUsing(fn () => 'global');

        // ponytail: declare every module feature flag so Pennant resolves it;
        // default ON. DB store persists toggles from the /features UI.
        foreach (array_keys(config('pennant.features', [])) as $slug) {
            Feature::define($slug, fn () => true);
        }

        Paginator::useBootstrap();
        LengthAwarePaginator::useBootstrap();

        User::observe(UserObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);

        // ponytail: Sentry DSN-gated; no-op locally when DSN empty
        if (class_exists(Sentry::class) && config('sentry.dsn')) {
            Sentry::init([
                'dsn' => config('sentry.dsn'),
                'release' => config('app.version'),
                'traces_sample_rate' => (float) config('sentry.traces_sample_rate', 0.2),
            ]);
        }

        // Header notifications: native Laravel notifications (database channel)
        View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $items = $user->notifications()->latest()->limit(5)->get();
                $view->with('notifications', [
                    'items' => $items,
                    'unread' => $user->unreadNotifications()->count(),
                ]);
            } else {
                $view->with('notifications', ['items' => collect(), 'unread' => 0]);
            }
        });
    }
}
