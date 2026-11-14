<?php

declare(strict_types=1);

namespace App\Domain\Currency;

/**
 * Accès aux devises et taux persistés (Supabase).
 * Séparé du convertisseur : la logique métier ignore le stockage (SOLID - DIP).
 */
interface CurrencyRepository
{
    /**
     * @return array<string, array<string, mixed>> indexé par code ISO
     */
    public function listCurrencies(): array;

    /**
     * Taux direct (1 base = ? quote) trouvé en base : manuel (utilisateur/foyer)
     * prioritaire, puis global. Retourne null si absent.
     */
    public function findRate(
        string $baseCurrency,
        string $quoteCurrency,
        ?string $profileId = null,
        ?string $householdId = null,
    ): ?ExchangeRate;
}
