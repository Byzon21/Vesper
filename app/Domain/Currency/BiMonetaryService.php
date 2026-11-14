<?php

declare(strict_types=1);

namespace App\Domain\Currency;

/**
 * Unification bimétaire (cas RDC USD/CDF).
 *
 * Convertit chaque jambe d'une allocation dans la devise de base du foyer, puis
 * somme les montants pour produire un total unifié utilisé par les rapports
 * globaux. Le taux peut être dynamique (provider) ou configuré (manuel).
 */
final class BiMonetaryService
{
    public function __construct(
        private readonly CurrencyConverter $converter,
        private readonly ExchangeRateService $rates,
    ) {
    }

    public function unify(
        BiMonetaryAllocation $allocation,
        string $baseCurrency,
        ?string $profileId = null,
        ?string $householdId = null,
    ): BiMonetarySummary {
        $baseCurrency = strtoupper($baseCurrency);
        $legs = [];
        $total = new Money('0', $baseCurrency);

        foreach ($allocation->legs as $leg) {
            if ($leg->currency === $baseCurrency) {
                $converted = $leg;
                $rate = new ExchangeRate($baseCurrency, $baseCurrency, '1', 'identity', true);
            } else {
                // rate(leg → base) = "1 leg = X base" ; convert = leg * X.
                $rate = $this->rates->rate($leg->currency, $baseCurrency, $profileId, $householdId);
                $converted = $this->converter->convert($leg, $rate, $baseCurrency);
            }

            $legs[] = new ConvertedLeg($leg, $converted, $rate);
            $total = $total->add($converted);
        }

        return new BiMonetarySummary($legs, $total, $baseCurrency);
    }
}
