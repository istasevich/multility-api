<?php
declare(strict_types=1);

namespace App\Domain\Contract;

use App\Domain\Exception\RateNotAvailableException;
use DateTimeInterface;

/**
 * Контракт источника курсов.
 *
 * Базовые принципы:
 * - Точность: курс возвращается как decimal-строка (e.g. "43210.123456").
 * - Универсальность: поддерживает фиат и крипто (например, "USD", "EUR", "BTC", "ETH").
 * - Временная семантика: опциональная метка времени "на момент", на MVP можно игнорировать и брать текущий.
 *
 * Пример использования:
 * $rate = $provider->getRate('BTC', 'USD');        // "68231.120045"
 * $rates = $provider->getRates('BTC', ['USD','EUR']); // ['USD' => '68231.12', 'EUR' => '64521.77']
 */
interface RateProvider
{
    /**
     * Возвращает единичный курс base->quote как decimal-строку.
     *
     * @param non-empty-string      $base    Базовая валюта/актив (e.g. "BTC", "USD")
     * @param non-empty-string      $quote   Котируемая валюта/актив (e.g. "USD", "EUR")
     * @param DateTimeInterface|null $at     Опционально: момент времени (для MVP можно игнорировать)
     *
     * @return non-empty-string Decimal как строка (без экспоненциальной формы), например "12345.6789"
     *
     * @throws RateNotAvailableException Если курс недоступен у провайдера
     */
    public function getRate(string $base, string $quote, ?DateTimeInterface $at = null): string;

    /**
     * Батчевое получение котировок для одного base в нескольких quote.
     *
     * @param non-empty-string       $base
     * @param list<non-empty-string> $quotes
     * @param DateTimeInterface|null $at
     *
     * @return array<non-empty-string, non-empty-string> Ассоц. массив: quote => decimal-строка
     *
     * @throws RateNotAvailableException Если хотя бы одна запрошенная котировка недоступна
     */
    public function getRates(string $base, array $quotes, ?DateTimeInterface $at = null): array;

    /**
     * Идентификатор/человеческое имя провайдера (для логов/метрик).
     *
     * @return non-empty-string
     */
    public function name(): string;

    /**
     * Быстрая проверка: способен ли провайдер отдать конкретную пару.
     * Можно использовать для короткого-пути при валидации запросов.
     */
    public function supports(string $base, string $quote): bool;
}
