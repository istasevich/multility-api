<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

final class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // Для всех API-запросов — JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error'   => class_basename($e),
                'message' => $e->getMessage(),
            ], 500);
        }

        return parent::render($request, $e);
    }
}
