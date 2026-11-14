<?php

declare(strict_types=1);

namespace App\Support\Security\Crypto;

/**
 * Valeur chiffrée (Field-Level Encryption) sérialisable en base.
 *
 * Format versionné stocké en colonne TEXT :  "ves1:<nonce_b64>:<ciphertext_b64>"
 *  - ves1        : version du format (permet la rotation future)
 *  - nonce_b64   : nonce AES-256-GCM (12 octets) encodé base64url
 *  - ciphertext  : ciphertext + tag d'authentification (GCM) encodé base64url
 *
 * Le tag GCM garantit l'intégrité : toute modification du ciphertext ou du
 * nonce entraîne un échec d'authentification au déchiffrement.
 */
final readonly class EncryptedField
{
    private const VERSION = 'ves1';

    private function __construct(
        private string $nonce,
        private string $ciphertext,
    ) {
    }

    public static function fromParts(string $nonce, string $ciphertext): self
    {
        return new self($nonce, $ciphertext);
    }

    /**
     * Désérialise depuis le format stocké en base.
     */
    public static function fromString(string $serialized): self
    {
        $parts = explode(':', $serialized, 3);

        if (count($parts) !== 3 || $parts[0] !== self::VERSION) {
            throw new CryptoException('Format de champ chiffré invalide.');
        }

        $nonce = self::b64Decode($parts[1]);
        $ciphertext = self::b64Decode($parts[2]);

        if ($nonce === '' || $ciphertext === '') {
            throw new CryptoException('Champ chiffré corrompu.');
        }

        return new self($nonce, $ciphertext);
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    public function ciphertext(): string
    {
        return $this->ciphertext;
    }

    public function toString(): string
    {
        return self::VERSION . ':' . self::b64Encode($this->nonce) . ':' . self::b64Encode($this->ciphertext);
    }

    private static function b64Encode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private static function b64Decode(string $encoded): string
    {
        $decoded = base64_decode(strtr($encoded, '-_', '+/'), true);

        return $decoded === false ? '' : $decoded;
    }
}
