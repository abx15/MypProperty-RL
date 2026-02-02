<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\RateLimiter as RateLimiterFacade;
use Symfony\Component\HttpFoundation\Response;

class RateLimiting
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, $next)
    {
        $key = $this->resolveRequestSignature($request);
        
        // Different rate limits for different endpoints
        if ($request->is('api/v1/register') || $request->is('api/v1/login')) {
            $maxAttempts = 5;
            $decayMinutes = 1;
        } elseif ($request->is('api/v1/ai/*')) {
            $maxAttempts = 20;
            $decayMinutes = 1;
        } elseif (str_starts_with($request->path(), 'api/v1/')) {
            $maxAttempts = 60;
            $decayMinutes = 1;
        } else {
            $maxAttempts = 100;
            $decayMinutes = 1;
        }

        if (RateLimiterFacade::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many attempts. Please try again later.',
                'retry_after' => RateLimiterFacade::availableIn($key),
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiterFacade::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - RateLimiterFacade::attempts($key)));

        return $response;
    }

    /**
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature($request): string
    {
        if ($user = $request->user()) {
            return sha1($user->id . '|' . $request->ip() . '|' . $request->route()->getName());
        }

        return sha1($request->ip() . '|' . $request->route()->getName());
    }
}
