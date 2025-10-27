<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'db'    => false,
            'redis' => false,
        ];

        try {
            DB::select('select 1');
            $checks['db'] = true;
        } catch (\Throwable) {
        }
        try {
            Redis::connection()->ping();
            $checks['redis'] = true;
        } catch (\Throwable) {
        }

        return response()->json([
            'status' => ($checks['db'] && $checks['redis']) ? 'ok' : 'degraded',
            'checks' => $checks,
        ]);
    }
}
