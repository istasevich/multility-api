<?php

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AuthenticateWithApiKey;
use App\Http\Middleware\RateLimitMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.apikey' => AuthenticateWithApiKey::class,
            'rate.limit' => RateLimitMiddleware::class,
        ]);

        $middleware->appendToGroup('api', ForceJsonResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                $status = 500;

                // Если это HTTP-исключение — возьмём код оттуда
                if (method_exists($e, 'getStatusCode')) {
                    $status = $e->getStatusCode();
                }

                return response()->json([
                    'success' => false,
                    'error'   => class_basename($e),
                    'message' => $e->getMessage(),
                ], $status);
            }

            // если не JSON — пусть Laravel сам рисует HTML
            return null;
        });
    })->create();
