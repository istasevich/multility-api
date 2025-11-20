<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\UseCase\Document\GeneratePdfFromHtmlUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DocumentController extends Controller
{
    public function generate(Request $request, GeneratePdfFromHtmlUseCase $useCase): JsonResponse
    {
        $validated = $request->validate([
            'source' => ['required', 'string'],
            'format' => ['nullable', 'string', 'in:A4,A3,Letter'],
            'orientation' => ['nullable', 'string', 'in:portrait,landscape'],
        ]);

        $doc = $useCase->execute($validated['source'], $validated);

        return response()->json([
            'status' => 'ok',
            'path' => $doc->path,
            'url' => $doc->url,
            'mime' => $doc->mime,
            'size' => $doc->size,
            'provider' => $doc->provider,
        ]);
    }

    public function stream(string $path): StreamedResponse
    {
        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);

        return response()->stream(function () use ($disk, $path) {
            echo $disk->get($path);
        }, 200, [
            'Content-Type' => $disk->mimeType($path),
            'Content-Disposition' => 'inline; filename="'.basename($path).'"'
        ]);
    }
}
