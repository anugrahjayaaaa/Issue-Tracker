<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs 4xx client errors (except 404 noise) so bad requests like a 405
 * from a wrong HTTP method are visible in monitoring. 5xx are already
 * logged by Laravel's exception handler.
 */
class LogHttpErrors
{
    /**
     * Capture the response and log 4xx client errors (except 404) for monitoring.
     *
     * @param  Request  $request  Incoming HTTP request
     * @param  Closure  $next  Next middleware in the pipeline
     * @return Response The unchanged response (pass-through)
     *
     * @details
     * Logs to the daily log channel -> file: storage/logs/laravel-YYYY-MM-DD.log
     * (driver: config('logging.default'), default 'daily').
     * Context: url, method, ip, user_id.
     * 5xx are already logged by Laravel's exception handler; 404 is skipped
     * to avoid noise from crawlers / missing assets.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $status = $response->getStatusCode();
        if ($status >= 400 && $status < 500 && $status !== 404) {
            // WHERE: storage/logs/laravel-YYYY-MM-DD.log (daily channel)
            Log::warning('HTTP '.$status, [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
            ]);
        }

        return $response;
    }
}
