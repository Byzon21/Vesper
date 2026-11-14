<?php

declare(strict_types=1);

namespace App\Domain\Currency;

/**
 * Fournisseur de taux de change externe (ex: OpenExchangeRates, exchangerate.host).
 * La clé API est résolue via le Vault (jamais exposée au client).
 */
interface ExchangeRateProvider
{
    /**
     * Récupère le taux direct 1 base = ? quote, ou null si indisponible.
     */
    public function fetch(string $baseCurrency, string $quoteCurrency): ?ExchangeRate;
}
