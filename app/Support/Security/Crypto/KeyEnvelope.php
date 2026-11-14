<?php

declare(strict_types=1);

namespace App\Support\Security\Crypto;

/**
 * Enveloppe de clés (Key Wrapping) — hiérarchie VMK → KEK → DEK.
 *
 * Une Data Encryption Key (DEK, clé de chiffrement des données) n'est jamais
 * stockée en clair : elle est enveloppée (wrap) avec la KEK via AES-256-GCM.
 * La KEK ne réside jamais en base : elle est conservée dans le Vault (ou
 * dérivée), ce qui garantit qu'une fuite de la base seule est inexploitable.
 */
final class KeyEnvelope
{
    public const KEY_BYTES = 32;

    public function __construct(
        private readonly FieldCipher $cipher,
    ) {
    }

    /**
     * Génère une nouvelle Data Encryption Key (32 octets aléatoires).
     */
    public function generateDek(): string
    {
        return random_bytes(self::KEY_BYTES);
    }

    /**
     * Génère une nouvelle Key Encryption Key (32 octets aléatoires).
     */
    public function generateKek(): string
    {
        return random_bytes(self::KEY_BYTES);
    }

    /**
     * Enveloppe (chiffre) une DEK avec une KEK. Le résultat est stockable en base.
     */
    public function wrap(string $dek, string $kek, string $context = ''): string
    {
        return $this->cipher->encrypt($dek, $kek, $context)->toString();
    }

    /**
     * Désenveloppe (déchiffre) une DEK avec une KEK.
     */
    public function unwrap(string $wrappedDek, string $kek, string $context = ''): string
    {
        return $this->cipher->decrypt(EncryptedField::fromString($wrappedDek), $kek, $context);
    }
}
