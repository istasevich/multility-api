<?php

namespace App\Domain\Exception;

use RuntimeException;

final class DocumentGenerationException extends RuntimeException
{
    public static function because(string $reason, ?\Throwable $previous = null): self
    {
        return new self("Document generation failed: {$reason}", previous: $previous);
    }

    public static function fromThrowable(\Throwable $e): self
    {
        return new self('Document generation failed: '.$e->getMessage(), previous: $e);
    }
}
