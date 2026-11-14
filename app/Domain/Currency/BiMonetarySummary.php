<?php

declare(strict_types=1);

namespace App\Domain\Currency;

/**
 * Résultat de l'unification bimétaire : jambes converties + total unifié.
 */
final readonly class BiMonetarySummary
{
    /**
     * @param list<ConvertedLeg> $legs
     */
    public function __construct(
        public array $legs,
        public Money $unifiedTotal,
        public string $baseCurrency,
    ) {
    }
}
