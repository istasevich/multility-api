<?php
declare(strict_types=1);

namespace App\Domain\Exception;

use RuntimeException;

final class RateNotAvailableException extends RuntimeException
{
    public static function forPair(string $base, string $quote): self
    {
        return new self(sprintf('Rate for %s/%s not available', $base, $quote));
    }
}
