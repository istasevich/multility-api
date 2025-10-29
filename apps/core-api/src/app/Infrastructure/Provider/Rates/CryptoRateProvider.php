<?php
declare(strict_types=1);

namespace App\Infrastructure\Provider\Rates;

use App\Domain\Contract\RateProvider;
use App\Domain\Exception\RateNotAvailableException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use DateTimeInterface;

final class CryptoRateProvider implements RateProvider
{
    protected const string API_URL = 'https://api.coingecko.com/api/v3/simple/price';
    protected const int CACHE_TTL = 21; // seconds

    public function getRate(string $base, string $quote, ?DateTimeInterface $at = null): string
    {
        $rates = $this->getRates($base, [$quote], $at);

        if (!isset($rates[$quote])) {
            throw RateNotAvailableException::forPair($base, $quote);
        }

        return $rates[$quote];
    }

    public function getRates(string $base, array $quotes, ?DateTimeInterface $at = null): array
    {
        $base = strtolower($base);
        $quotesKey = implode(',', array_map('strtolower', $quotes));

        $cacheKey = sprintf('rates:%s:%s', $base, md5($quotesKey));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($base, $quotes) {
            $response = Http::timeout(5)->get(self::API_URL, [
                'ids' => $this->mapSymbolToId($base),
                'vs_currencies' => implode(',', $quotes),
            ]);

            if (!$response->successful()) {
                throw new RateNotAvailableException('CoinGecko API error: '.$response->status());
            }

            $data = $response->json();

            if (!isset($data[$this->mapSymbolToId($base)])) {
                throw RateNotAvailableException::forPair($base, implode(',', $quotes));
            }

            $result = [];
            foreach ($quotes as $quote) {
                $quoteLower = strtolower($quote);
                $value = $data[$this->mapSymbolToId($base)][$quoteLower] ?? null;
                if ($value === null) {
                    continue;
                }
                // Преобразуем в decimal-строку с контролем формата
                $result[$quote] = number_format((float) $value, 8, '.', '');
            }

            return $result;
        });
    }

    public function name(): string
    {
        return 'CoinGecko';
    }

    public function supports(string $base, string $quote): bool
    {
        // MVP: поддерживаем основные пары
        return in_array(strtoupper($base), ['BTC', 'ETH', 'USDT', 'USDC'], true)
            || in_array(strtoupper($quote), ['BTC', 'ETH', 'USDT', 'USDC', 'USD', 'EUR'], true);
    }

    /**
     * Маппинг тикера на id CoinGecko
     */
    protected function mapSymbolToId(string $symbol): string
    {
        return match (strtolower($symbol)) {
            'btc' => 'bitcoin',
            'eth' => 'ethereum',
            'usdt' => 'tether',
            'usdc' => 'usd-coin',
            default => strtolower($symbol),
        };
    }
}
