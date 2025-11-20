<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\UseCase\Document\GeneratePdfFromHtmlUseCase;
use App\Domain\Contract\DocumentJobStatusRepository;
use App\Jobs\Document\GeneratePdfFromHtmlJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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


    public function generateAsync(
        Request $request,
        DocumentJobStatusRepository $statusRepository,
    ): JsonResponse {
        $validated = $request->validate([
            'source'      => ['required', 'string'],
            'format'      => ['nullable', 'string', 'in:A4,A3,Letter'],
            'orientation' => ['nullable', 'string', 'in:portrait,landscape'],
        ]);

        $jobId = (string) Str::uuid();

        // создаём pending статус в Redis
        $statusRepository->createPending($jobId, type: 'pdf_from_html');

        GeneratePdfFromHtmlJob::dispatch(
            jobId: $jobId,
            source: $validated['source'],
            params: $validated,
            type: 'pdf_from_html',
        );

        return response()->json([
            'status'  => 'queued',
            'job_id'  => $jobId,
        ], 202);
    }

    public function status(
        string $jobId,
        DocumentJobStatusRepository $statusRepository,
    ): JsonResponse {
        $status = $statusRepository->get($jobId);

        if ($status === null) {
            return response()->json([
                'status'  => 'not_found',
                'message' => 'Job not found',
            ], 404);
        }

        return response()->json([
            'status' => $status->status,   // pending|processing|done|failed
            'type' => $status->type,
            'path' => $status->path,
            'url' => $status->url,
            'mime' => $status->mime,
            'size' => $status->size,
            'provider' => $status->provider,
            'error' => $status->error,
            'message' => $status->message,
        ]);
    }

    public function stream(string $path): StreamedResponse
    {
        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        return response()->stream(function () use ($disk, $path) {
            echo $disk->get($path);
        }, 200, [
            'Content-Type' => $disk->mimeType($path),
            'Content-Disposition' => 'inline; filename="'.basename($path).'"'
        ]);
    }
}
