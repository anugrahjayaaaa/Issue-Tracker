<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replaces seanbarton/laravel-periscope's default Authorize middleware
 * (which hardwires Telescope::check -> the viewTelescope gate) so Periscope
 * can have its own access gate, independent of Telescope. Bound in
 * TelescopeServiceProvider::register().
 */
class PeriscopeAuthorize
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Gate::check('viewPeriscope', [$request->user()])) {
            return $next($request);
        }

        abort(403);
    }
}
