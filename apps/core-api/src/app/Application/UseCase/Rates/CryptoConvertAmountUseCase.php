<?php
declare(strict_types=1);

namespace App\Application\UseCase\Rates;

use App\Domain\Contract\RateProvider;
use App\Domain\Exception\RateNotAvailableException;

class CryptoConvertAmountUseCase
{
    public function __construct(
        protected RateProvider $provider,
    ) {
        // Nothing
    }

    /**
     * Конвертирует сумму base→quote
     *
     * @param   string            $base   (e.g. "BTC")
     * @param   string            $quote  (e.g. "USD")
     * @param   string|float|int  $amount
     *
     * @return string
     */
    public function execute(string $base, string $quote, string|float|int $amount): string
    {
        $rate = $this->provider->getRate($base, $quote);

        // точное decimal-умножение
        return bcmul((string)$amount, $rate, 8);
    }
}
