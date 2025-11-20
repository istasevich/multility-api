<?php

namespace App\Domain\Contract;

use App\Domain\DTO\DocumentJobStatusDto;
use App\Domain\DTO\GeneratedDocumentDto;

interface DocumentJobStatusRepository
{
    public function createPending(string $id, string $type): void;

    public function markProcessing(string $id): void;

    public function markCompleted(string $id, GeneratedDocumentDto $document): void;

    public function markFailed(string $id, string $error, ?string $message = null): void;

    public function get(string $id): ?DocumentJobStatusDto;
}
