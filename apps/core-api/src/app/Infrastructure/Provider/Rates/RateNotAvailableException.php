<?php

namespace app\Infrastructure\Provider\Rates;

use RuntimeException;

final class RateNotAvailableException extends RuntimeException
{
    public static function forPair(string $base, string $quote): self
    {
        return new self(sprintf('Rate for %s/%s not available', $base, $quote));
    }
}
