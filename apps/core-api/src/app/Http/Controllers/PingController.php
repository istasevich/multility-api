<?php

namespace App\Http\Controllers;

use App\Application\UseCase\PingUseCase;
use Illuminate\Http\JsonResponse;

final class PingController extends Controller
{
    public function __invoke(PingUseCase $useCase): JsonResponse
    {
        return response()->json($useCase->handle());
    }
}
