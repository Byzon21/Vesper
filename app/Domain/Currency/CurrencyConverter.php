<?php

declare(strict_types=1);

namespace App\Domain\Currency;

/**
 * Convertisseur de devises (arithmétique exacte BCMath).
 *
 * Convertit un montant d'une devise à une autre à l'aide d'un taux. Couvre la
 * paire directe et son inverse. La triangulation multi-paires est orchestrée
 * par ExchangeRateService (voir sa méthode rate()).
 */
final class CurrencyConverter
{
    public function convert(Money $money, ExchangeRate $rate, string $targetCurrency): Money
    {
        $targetCurrency = strtoupper($targetCurrency);

        if ($money->currency === $rate->baseCurrency && $targetCurrency === $rate->quoteCurrency) {
            return $money->multiply($rate->rate);
        }

        if ($money->currency === $rate->quoteCurrency && $targetCurrency === $rate->baseCurrency) {
            return $money->divide($rate->rate);
        }

        throw new \InvalidArgumentException(
            "Le taux {$rate} ne couvre pas la conversion {$money->currency} → {$targetCurrency}."
        );
    }

    /**
     * Convertit puis arrondit au nombre de décimales de la devise cible.
     */
    public function convertRounded(Money $money, ExchangeRate $rate, string $targetCurrency, int $decimals): Money
    {
        return $this->convert($money, $rate, $targetCurrency)->rounded($decimals);
    }
}
