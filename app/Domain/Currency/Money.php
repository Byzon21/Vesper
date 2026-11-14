<?php

declare(strict_types=1);

namespace App\Domain\Currency;

/**
 * Montant monétaire immuable (Value Object).
 *
 * L'argent est représenté par une CHAÎNE décimale (ex: "40.00", "25000") et non
 * par un float, pour éviter toute erreur d'arrondi binaire (précision exigée en
 * finance). Toutes les opérations passent par BCMath (arithmétique exacte).
 */
final readonly class Money
{
    public const SCALE = 8;

    public function __construct(
        public string $amount,
        public string $currency,
    ) {
        if (! self::isValidAmount($this->amount)) {
            throw new \InvalidArgumentException("Montant invalide : {$this->amount}");
        }

        if (strlen($this->currency) !== 3) {
            throw new \InvalidArgumentException("Code devise invalide : {$this->currency}");
        }
    }

    public static function isValidAmount(string $amount): bool
    {
        return (bool) preg_match('/^\d+(\.\d+)?$/', $amount);
    }

    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);

        return new self(bcadd($this->amount, $other->amount, self::SCALE), $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);

        return new self(bcsub($this->amount, $other->amount, self::SCALE), $this->currency);
    }

    public function multiply(string $factor): self
    {
        return new self(bcmul($this->amount, $factor, self::SCALE), $this->currency);
    }

    public function divide(string $divisor): self
    {
        if (bccomp($divisor, '0', self::SCALE) === 0) {
            throw new \InvalidArgumentException('Division par zéro.');
        }

        return new self(bcdiv($this->amount, $divisor, self::SCALE), $this->currency);
    }

    public function isZero(): bool
    {
        return bccomp($this->amount, '0', self::SCALE) === 0;
    }

    public function isNegative(): bool
    {
        return bccomp($this->amount, '0', self::SCALE) < 0;
    }

    public function rounded(int $decimals = 2): self
    {
        return new self(number_format((float) $this->amount, $decimals, '.', ''), $this->currency);
    }

    public function __toString(): string
    {
        return $this->amount . ' ' . $this->currency;
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException(
                "Devises incompatibles : {$this->currency} / {$other->currency}"
            );
        }
    }
}
