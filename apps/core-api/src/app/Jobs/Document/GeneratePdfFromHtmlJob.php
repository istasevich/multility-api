<?php

namespace App\Jobs\Document;

use App\Application\UseCase\Document\GeneratePdfFromHtmlUseCase;
use App\Domain\Contract\DocumentJobStatusRepositoryInterface;
use App\Domain\Enums\DocumentJobStatusEnum;
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
        DocumentJobStatusRepositoryInterface $statusRepository,
    ): void {

        $statusRepository->updateStatus($this->jobId, DocumentJobStatusEnum::Running);

        try {
            $document = $useCase->execute($this->source, $this->params);
            $statusRepository->markFinished($this->jobId, $document);
        } catch (Throwable $e) {
            $statusRepository->markFailed(
                jobId: $this->jobId,
                error: $e,
            );

            throw $e;
        }
    }
}
