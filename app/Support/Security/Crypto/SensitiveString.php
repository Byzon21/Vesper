<?php

declare(strict_types=1);

namespace App\Support\Security\Crypto;

/**
 * Chaîne de caractères sensible auto-destructrice.
 *
 * Garantit qu'un secret (clé, token, montant déchiffré) n'est jamais :
 *  - sérialisé (session, cache, queue, logs),
 *  - affiché dans var_dump()/debugInfo,
 *  - conservé en mémoire au-delà de sa durée de vie.
 *
 * La mémoire est écrasée (zeroize) à la destruction de l'objet.
 */
final class SensitiveString implements \Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Retourne la valeur brute — à n'utiliser qu'au moment exact où elle est
     * nécessaire, puis laisser l'objet sortir du scope pour un zeroize.
     */
    public function reveal(): string
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return $this->value === '';
    }

    /**
     * Efface immédiatement la valeur de la mémoire.
     */
    public function erase(): void
    {
        if (isset($this->value) && $this->value !== '') {
            sodium_memzero($this->value);
        }
        unset($this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function __destruct()
    {
        $this->erase();
    }

    /**
     * Interdiction formelle de sérialisation (anti-exfiltration).
     */
    public function __serialize(): array
    {
        throw new \LogicException('Un secret ne peut pas être sérialisé.');
    }

    /**
     * Interdiction de reprise après unserialize.
     */
    public function __unserialize(array $data): void
    {
        throw new \LogicException('Un secret ne peut pas être désérialisé.');
    }

    public function __debugInfo(): array
    {
        return ['value' => '[REDACTED]'];
    }
}
