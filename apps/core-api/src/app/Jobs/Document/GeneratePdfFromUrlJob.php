<?php

namespace app\Jobs\Document;

use App\Application\UseCase\Document\GeneratePdfFromUrlUseCase;
use App\Domain\Contract\DocumentJobStatusRepositoryInterface;
use App\Domain\DTO\GeneratedDocumentDto;
use App\Domain\Enums\DocumentJobStatusEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

final class GeneratePdfFromUrlJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $jobId,
        public readonly string $url,
        /** @var array<string, mixed> */
        public readonly array $params = [],
    ) {
        // Nothing
    }

    /**
     * @throws Throwable
     */
    public function handle(
        GeneratePdfFromUrlUseCase $useCase,
        DocumentJobStatusRepositoryInterface $statusRepository,
    ): void {
        $statusRepository->updateStatus($this->jobId, DocumentJobStatusEnum::Running);

        try {
            $document = $useCase->execute(
                $this->url,
                ['mode' => 'url'] + $this->params
            );

            $statusRepository->markFinished($this->jobId, $document);
        } catch (Throwable $e) {
            $statusRepository->markFailed($this->jobId, $e);
            throw $e;
        }
    }
}
