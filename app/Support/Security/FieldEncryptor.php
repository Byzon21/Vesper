<?php

declare(strict_types=1);

namespace App\Support\Security;

use App\Support\Security\Crypto\EncryptedField;
use App\Support\Security\Crypto\FieldCipher;

/**
 * Chiffreur de champs de haut niveau (Field-Level Encryption).
 *
 * Chiffre/déchiffre une valeur en liant le ciphertext à son contexte
 * (propriétaire + table + colonne) via l'AAD. Empêche la copie d'une valeur
 * chiffrée d'une ligne vers une autre (attaque par substitution).
 *
 * Les montants, soldes et notes personnelles transitent TOUJOURS par cette
 * classe avant insertion/lecture en base.
 */
final class FieldEncryptor
{
    public function __construct(
        private readonly FieldCipher $cipher,
    ) {
    }

    public function encrypt(string $plaintext, string $dek, string $owner, string $table, string $column): string
    {
        return $this->cipher
            ->encrypt($plaintext, $dek, $this->aad($owner, $table, $column))
            ->toString();
    }

    public function decrypt(string $serialized, string $dek, string $owner, string $table, string $column): string
    {
        return $this->cipher->decrypt(
            EncryptedField::fromString($serialized),
            $dek,
            $this->aad($owner, $table, $column)
        );
    }

    private function aad(string $owner, string $table, string $column): string
    {
        return sprintf('vesper:%s:%s:%s', $table, $column, $owner);
    }
}
