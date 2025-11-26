<?php

namespace App\Domain\Contract;

use App\Domain\DTO\DocumentJobStatusDto;
use App\Domain\DTO\GeneratedDocumentDto;
use App\Domain\Enums\DocumentJobStatusEnum;

interface DocumentJobStatusRepositoryInterface
{
    /**
     * Создаёт начальную запись о задаче.
     */
    public function create(
        string $jobId,
        string $type,
        DocumentJobStatusEnum $status = DocumentJobStatusEnum::Pending
    ): void;

    /**
     * Обновляет статус задачи без payload.
     */
    public function updateStatus(
        string $jobId,
        DocumentJobStatusEnum $status
    ): void;

    /**
     * Помечает задачу выполненной и сохраняет результат.
     */
    public function markFinished(
        string $jobId,
        GeneratedDocumentDto $document
    ): void;

    /**
     * Помечает задачу упавшей.
     */
    public function markFailed(
        string $jobId,
        \Throwable $error
    ): void;

    /**
     * Получает данные по задаче.
     */
    public function get(string $jobId): ?DocumentJobStatusDto;
}
