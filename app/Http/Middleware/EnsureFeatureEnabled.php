<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access to a route unless its feature flag is enabled (Laravel Pennant).
 * Pair with `can:` so the full gate is: has permission AND feature on.
 * Unknown feature => 404 (fail-closed), never silently allowed.
 *
 * A disabled flag blocks everyone (true kill-switch), including `feature.manage`
 * holders — they manage flags from the `/features` UI, not from the disabled module.
 */
class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        if (! Feature::active($slug)) {
            abort(404);
        }

        return $next($request);
    }
}
