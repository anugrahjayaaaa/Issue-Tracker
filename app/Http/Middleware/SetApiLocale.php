<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Resolves the API locale from a request header (stateless).
 * Honors X-Locale first, then Accept-Language; falls back to app locale.
 */
class SetApiLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('X-Locale')
            ?? $request->getPreferredLanguage(config('app.available_locales'))
            ?? config('app.locale');

        if (! in_array($locale, config('app.available_locales'), true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
