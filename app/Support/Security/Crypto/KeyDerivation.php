<?php

declare(strict_types=1);

namespace App\Support\Security\Crypto;

/**
 * Dérivation de clés via Argon2id (recommandation OWASP).
 *
 * Transforme un secret de faible entropie (passphrase, mot de passe) en une
 * clé cryptographique de 32 octets, avec un sel unique et des paramètres
 * mémoire/temps configurables pour contrer les attaques par force brute
 * matérielle (GPU/ASIC).
 */
final class KeyDerivation
{
    public const KEY_BYTES = 32;
    public const SALT_BYTES = SODIUM_CRYPTO_PWHASH_SALTBYTES; // 16 octets

    /**
     * Dérive une clé de 32 octets à partir d'un secret et d'un sel.
     *
     * @param string $secret   secret d'entrée (passphrase utilisateur ou seed)
     * @param string $salt     sel unique de 16 octets (SODIUM_CRYPTO_PWHASH_SALTBYTES)
     */
    public function deriveKey(
        string $secret,
        string $salt,
        int $opsLimit = SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
        int $memLimit = SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
    ): string {
        if (strlen($salt) !== self::SALT_BYTES) {
            throw new CryptoException(sprintf('Le sel doit faire %d octets.', self::SALT_BYTES));
        }

        return sodium_crypto_pwhash(
            self::KEY_BYTES,
            $secret,
            $salt,
            $opsLimit,
            $memLimit,
            SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
        );
    }

    /**
     * Génère un sel aléatoire adapté à Argon2id.
     */
    public function generateSalt(): string
    {
        return random_bytes(self::SALT_BYTES);
    }
}
