<?php

namespace App\Infrastructure\Persistence\Document;

use App\Domain\Contract\DocumentJobStatusRepositoryInterface;
use App\Domain\DTO\DocumentJobStatusDto;
use App\Domain\DTO\GeneratedDocumentDto;
use App\Domain\Enums\DocumentJobStatusEnum;
use Illuminate\Support\Facades\Redis;

final class RedisDocumentJobStatusRepository implements DocumentJobStatusRepositoryInterface
{
    private const PREFIX = 'document_job:';
    private const TTL = 86400; // 24h

    public function create(
        string $jobId,
        string $type,
        DocumentJobStatusEnum $status = DocumentJobStatusEnum::Pending
    ): void {
        $this->write($jobId, [
            'status' => $status->value,
            'type' => $type,
            'created_at' => now()->toIso8601String(),
        ]);
    }

    public function updateStatus(string $jobId, DocumentJobStatusEnum $status): void
    {
        $this->write($jobId, [
            'status' => $status->value,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function markFinished(string $jobId, GeneratedDocumentDto $document): void
    {
        $this->write($jobId, [
            'status' => DocumentJobStatusEnum::Done->value,
            'path' => $document->path,
            'url' => $document->url,
            'mime' => $document->mime,
            'size' => (string)$document->size,
            'provider' => $document->provider,
            'finished_at' => now()->toIso8601String(),
        ]);
    }

    public function markFailed(string $jobId, \Throwable $error): void
    {
        $this->write($jobId, [
            'status' => DocumentJobStatusEnum::Failed->value,
            'error' => $error::class,
            'message' => $error->getMessage(),
            'finished_at' => now()->toIso8601String(),
        ]);
    }

    public function get(string $jobId): ?DocumentJobStatusDto
    {
        $key = $this->key($jobId);

        if (! Redis::exists($key)) {
            return null;
        }

        /** @var array<string,string> $data */
        $data = Redis::hgetall($key);

        return DocumentJobStatusDto::fromArray($jobId, $data);
    }

    // ===================================================================================
    // Internal helpers
    // ===================================================================================

    /**
     * @param array<string,scalar|null> $data
     */
    private function write(string $jobId, array $data): void
    {
        $key = $this->key($jobId);

        foreach ($data as $field => $value) {
            if ($value === null) {
                continue;
            }
            Redis::hset($key, $field, (string) $value);
        }

        Redis::expire($key, self::TTL);
    }

    private function key(string $jobId): string
    {
        return self::PREFIX . $jobId;
    }
}
