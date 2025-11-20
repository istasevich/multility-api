<?php

namespace app\Domain\DTO;

namespace App\Domain\DTO;

final readonly class DocumentJobStatusDto
{
    public function __construct(
        public string $id,
        public string $status,   // pending|processing|done|failed
        public string $type,
        // например: pdf_from_html, pdf_from_markdown и т.п.
        public ?string $path = null,
        public ?string $url = null,
        public ?string $mime = null,
        public ?int $size = null,
        public ?string $provider = null,
        public ?string $error = null,
        public ?string $message = null,
    ) {
        // Nothing
    }

    /**
     * @param   array<string, mixed>  $data
     */
    public static function fromArray(string $id, array $data): self
    {
        return new self(
            id: $id,
            status: $data['status'] ?? 'pending',
            type: $data['type'] ?? 'unknown',
            path: $data['path'] ?? null,
            url: $data['url'] ?? null,
            mime: $data['mime'] ?? null,
            size: isset($data['size']) ? (int)$data['size'] : null,
            provider: $data['provider'] ?? null,
            error: $data['error'] ?? null,
            message: $data['message'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'type' => $this->type,
            'path' => $this->path,
            'url' => $this->url,
            'mime' => $this->mime,
            'size' => $this->size,
            'provider' => $this->provider,
            'error' => $this->error,
            'message' => $this->message,
        ];
    }
}

