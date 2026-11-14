<?php

declare(strict_types=1);

namespace App\Domain\Currency;

/**
 * Allocation bimétaire (Value Object) — cas RDC (USD + CDF).
 *
 * Représente la saisie multi-devises d'une transaction : l'utilisateur renseigne
 * une ou plusieurs "jambes", chacune dans sa propre devise.
 * Exemple : [40 USD, 25 000 CDF] pour une seule dépense.
 */
final class BiMonetaryAllocation
{
    /**
     * @param list<Money> $legs
     */
    public function __construct(
        public array $legs,
    ) {
        if ($legs === []) {
            throw new \InvalidArgumentException('Une allocation doit comporter au moins une jambe.');
        }

        foreach ($legs as $leg) {
            if (! $leg instanceof Money) {
                throw new \InvalidArgumentException('Jambe monétaire invalide.');
            }
            if ($leg->isNegative() || $leg->isZero()) {
                throw new \InvalidArgumentException('Une jambe doit être strictement positive.');
            }
        }
    }

    /**
     * Devises distinctes de l'allocation (ex: ['USD', 'CDF']).
     *
     * @return list<string>
     */
    public function currencies(): array
    {
        return array_values(array_unique(array_map(
            static fn (Money $leg): string => $leg->currency,
            $this->legs,
        )));
    }

    public function isBiMonetary(): bool
    {
        return count($this->currencies()) > 1;
    }
}
