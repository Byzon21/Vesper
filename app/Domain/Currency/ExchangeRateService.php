<?php

declare(strict_types=1);

namespace App\Domain\Currency;

/**
 * Résolution des taux de change avec stratégie de repli :
 *
 *   1. Taux manuel persistant (utilisateur/foyer) — prioritaire (configurable)
 *   2. Taux inverse persistant
 *   3. Fournisseur externe (clé via Vault) — direct puis inverse
 *   4. Triangulation via le pivot USD
 *
 * Un cache en mémoire évite les appels répétés au sein d'une requête.
 */
final class ExchangeRateService
{
    /** @var array<string, ExchangeRate> */
    private array $cache = [];

    public function __construct(
        private readonly CurrencyRepository $repository,
        private readonly ?ExchangeRateProvider $provider = null,
    ) {
    }

    public function rate(
        string $baseCurrency,
        string $quoteCurrency,
        ?string $profileId = null,
        ?string $householdId = null,
    ): ExchangeRate {
        $base = strtoupper($baseCurrency);
        $quote = strtoupper($quoteCurrency);

        if ($base === $quote) {
            return new ExchangeRate($base, $quote, '1', 'identity', true);
        }

        $key = "{$base}|{$quote}|{$profileId}|{$householdId}";

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        // 1. Taux direct persistant (manuel prioritaire)
        $rate = $this->repository->findRate($base, $quote, $profileId, $householdId);

        // 2. Taux inverse persistant
        if ($rate === null) {
            $inverse = $this->repository->findRate($quote, $base, $profileId, $householdId);
            $rate = $inverse?->inverse();
        }

        // 3. Fournisseur externe
        if ($rate === null && $this->provider !== null) {
            $rate = $this->provider->fetch($base, $quote)
                ?? $this->provider->fetch($quote, $base)?->inverse();
        }

        // 4. Triangulation via USD
        if ($rate === null && $base !== 'USD' && $quote !== 'USD') {
            $usdToBase = $this->rate('USD', $base, $profileId, $householdId);
            $usdToQuote = $this->rate('USD', $quote, $profileId, $householdId);

            $rate = new ExchangeRate(
                $base,
                $quote,
                bcdiv($usdToQuote->rate, $usdToBase->rate, Money::SCALE),
                'triangulation',
                false,
            );
        }

        if ($rate === null) {
            throw new \RuntimeException("Taux de change indisponible : {$base}/{$quote}");
        }

        return $this->cache[$key] = $rate;
    }
}
