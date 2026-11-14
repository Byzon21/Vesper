<?php

declare(strict_types=1);

namespace App\Domain\Currency;

/**
 * Taux de change (Value Object).
 *
 * `rate` signifie : 1 unité de baseCurrency = `rate` unités de quoteCurrency.
 * Exemple : base=USD, quote=CDF, rate="2800.00000000" → 1 USD = 2 800 CDF.
 */
final readonly class ExchangeRate
{
    public function __construct(
        public string $baseCurrency,
        public string $quoteCurrency,
        public string $rate,
        public string $source = 'manual',
        public bool $isManual = true,
    ) {
        if (! Money::isValidAmount($this->rate) || bccomp($this->rate, '0', Money::SCALE) <= 0) {
            throw new \InvalidArgumentException('Taux de change invalide.');
        }
    }

    /**
     * Taux inverse : 1 quote = 1/rate base.
     */
    public function inverse(): self
    {
        $inverse = bcdiv('1', $this->rate, Money::SCALE);

        return new self(
            $this->quoteCurrency,
            $this->baseCurrency,
            $inverse,
            $this->source,
            $this->isManual,
        );
    }

    public function __toString(): string
    {
        return "1 {$this->baseCurrency} = {$this->rate} {$this->quoteCurrency}";
    }
}
