<?php

declare(strict_types=1);

namespace App\Domain\Currency;

/**
 * Jambe convertie : montant d'origine + équivalent en devise de base + taux utilisé.
 */
final readonly class ConvertedLeg
{
    public function __construct(
        public Money $original,
        public Money $converted,
        public ExchangeRate $rate,
    ) {
    }
}
