<?php

namespace app\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

final class RateLimitMiddleware
{
    public function handle(Request $request, \Closure $next): Response
    {
        $key = 'rate:'.sha1($request->ip());

        if (RateLimiter::tooManyAttempts($key, 60)) {
            return response()->json(['error' => 'Too many requests'], 429);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
