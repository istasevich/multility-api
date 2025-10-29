<?php

namespace app\Http\Controllers\Api;

use App\Application\UseCase\Rates\CryptoConvertAmountUseCase;
use App\Domain\Exception\RateNotAvailableException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class RateController extends Controller
{
    public function convert(Request $request, CryptoConvertAmountUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'base' => ['required', 'string', 'size:3'],
            'quote' => ['required', 'string', 'size:3'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $converted = $useCase->execute(
                strtoupper($validated['base']),
                strtoupper($validated['quote']),
                (string)$validated['amount']
            );
        } catch (RateNotAvailableException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'base' => $validated['base'],
            'quote' => $validated['quote'],
            'amount' => $validated['amount'],
            'converted' => $converted,
        ]);
    }
}
