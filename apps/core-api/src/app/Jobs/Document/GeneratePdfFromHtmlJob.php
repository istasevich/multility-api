<?php

namespace App\Jobs\Document;

use App\Application\UseCase\Document\GeneratePdfFromHtmlUseCase;
use App\Domain\Contract\DocumentJobStatusRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class GeneratePdfFromHtmlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $jobId,
        public string $source,
        public array $params = [],
        public string $type = 'pdf_from_html',
    ) {
        // Nothing
    }

    /**
     * @throws Throwable
     */
    public function handle(
        GeneratePdfFromHtmlUseCase $useCase,
        DocumentJobStatusRepository $statusRepository,
    ): void {
        $statusRepository->markProcessing($this->jobId);

        try {
            $document = $useCase->execute($this->source, $this->params);
            $statusRepository->markCompleted($this->jobId, $document);
        } catch (Throwable $e) {
            $statusRepository->markFailed(
                id: $this->jobId,
                error: $e::class,
                message: $e->getMessage(),
            );

            throw $e;
        }
    }
}
