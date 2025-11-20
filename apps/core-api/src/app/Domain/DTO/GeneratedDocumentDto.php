<?php
declare(strict_types=1);

namespace App\Domain\DTO;

final readonly class GeneratedDocumentDto
{
    public function __construct(
        public string $path,   // абсолютный путь или storage path
        public string $url,    // публичный URL (если доступен)
        public string $mime,
        public int $size,
        public string $provider,
    ) {
        // Nothing
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
