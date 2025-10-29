<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;

final class AuthenticateWithApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->header('X-API-Key');

        if (!$key || !($apiKey = ApiKey::where('key', $key)->first())) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $request->merge(['user' => $apiKey->user]);
        $apiKey->increment('usage_count', 1);
        $apiKey->update(['last_used_at' => now()]);

        return $next($request);
    }
}
