<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Document;

use App\Domain\Contract\DocumentJobStatusRepository;
use App\Domain\DTO\DocumentJobStatusDto;
use App\Domain\DTO\GeneratedDocumentDto;
use Illuminate\Support\Facades\Redis;

final class RedisDocumentJobStatusRepository implements DocumentJobStatusRepository
{
    private const PREFIX = 'document_job:';
    private const TTL = 86400; // 24 часа

    public function createPending(string $id, string $type): void
    {
        $key = $this->key($id);

        $this->writeHash($key, [
            'status'     => 'pending',
            'type'       => $type,
            'created_at' => now()->toIso8601String(),
        ]);

        Redis::expire($key, self::TTL);
    }

    public function markProcessing(string $id): void
    {
        $key = $this->key($id);

        $this->writeHash($key, [
            'status'     => 'processing',
            'started_at' => now()->toIso8601String(),
        ]);

        Redis::expire($key, self::TTL);
    }

    public function markCompleted(string $id, GeneratedDocumentDto $document): void
    {
        $key = $this->key($id);

        $this->writeHash($key, [
            'status'      => 'done',
            'path'        => $document->path,
            'url'         => $document->url,
            'mime'        => $document->mime,
            'size'        => (string) $document->size,
            'provider'    => $document->provider,
            'finished_at' => now()->toIso8601String(),
        ]);

        Redis::expire($key, self::TTL);
    }

    public function markFailed(string $id, string $error, ?string $message = null): void
    {
        $key = $this->key($id);

        $this->writeHash($key, [
            'status'      => 'failed',
            'error'       => $error,
            'message'     => $message,
            'finished_at' => now()->toIso8601String(),
        ]);

        Redis::expire($key, self::TTL);
    }

    public function get(string $id): ?DocumentJobStatusDto
    {
        $key = $this->key($id);

        if (! Redis::exists($key)) {
            return null;
        }

        /** @var array<string, string> $data */
        $data = Redis::hgetall($key);

        return DocumentJobStatusDto::fromArray($id, $data);
    }

    private function key(string $id): string
    {
        return self::PREFIX.$id;
    }

    /**
     * @param array<string, scalar|null> $data
     */
    private function writeHash(string $key, array $data): void
    {
        foreach ($data as $field => $value) {
            if ($value === null) {
                continue;
            }

            Redis::hset($key, (string) $field, (string) $value);
        }
    }
}
