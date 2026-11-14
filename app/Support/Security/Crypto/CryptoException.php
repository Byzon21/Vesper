<?php

declare(strict_types=1);

namespace App\Support\Security\Crypto;

/**
 * Erreur cryptographique générique — ne doit jamais divulguer d'information
 * sensible dans son message (anti-oracle).
 */
class CryptoException extends \RuntimeException
{
    public static function tampered(string $context = 'donnée'): self
    {
        return new self(sprintf('La %s a été altérée ou la clé est invalide.', $context));
    }

    public static function invalidKeyLength(int $expected, int $actual): self
    {
        return new self(sprintf(
            'Longueur de clé invalide : attendu %d octets, reçu %d.',
            $expected,
            $actual
        ));
    }

    public static function unsupportedCipher(): self
    {
        return new self('AES-256-GCM n\'est pas disponible sur ce serveur (extension sodium requise).');
    }
}
