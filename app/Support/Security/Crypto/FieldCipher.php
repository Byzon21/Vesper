<?php

declare(strict_types=1);

namespace App\Support\Security\Crypto;

/**
 * Chiffrement de champ applicatif AES-256-GCM (authenticated encryption).
 *
 * Les données financières sensibles (montants, soldes, notes personnelles)
 * sont chiffrées AVANT leur insertion en base. Même un administrateur de la
 * base (ou une fuite de dump) ne peut lire les valeurs en clair sans la clé
 * applicative (DEK), elle-même enveloppée par une KEK résidant dans le Vault.
 *
 * L'AAD (Additional Authenticated Data) permet de "lier" le ciphertext à son
 * contexte (ex: user_id + table + colonne), empêchant le déplacement d'un
 * ciphertext d'une ligne à une autre (attaque par substitution/confusion).
 */
final class FieldCipher
{
    private const KEY_BYTES = SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES;     // 32 octets
    private const NONCE_BYTES = SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES;  // 12 octets

    public function __construct()
    {
        if (! function_exists('sodium_crypto_aead_aes256gcm_encrypt')) {
            throw CryptoException::unsupportedCipher();
        }
    }

    /**
     * Chiffre une valeur en clair avec la clé 32 octets fournie.
     *
     * @param string $aad Contexte d'authentification (données associées non chiffrées mais authentifiées)
     */
    public function encrypt(string $plaintext, string $key, string $aad = ''): EncryptedField
    {
        $this->assertKeyLength($key);

        $nonce = random_bytes(self::NONCE_BYTES);
        $ciphertext = sodium_crypto_aead_aes256gcm_encrypt($plaintext, $aad, $nonce, $key);

        return EncryptedField::fromParts($nonce, $ciphertext);
    }

    /**
     * Déchiffre un champ chiffré. Lève une exception si l'intégrité est rompue.
     */
    public function decrypt(EncryptedField $field, string $key, string $aad = ''): string
    {
        $this->assertKeyLength($key);

        $plaintext = sodium_crypto_aead_aes256gcm_decrypt(
            $field->ciphertext(),
            $aad,
            $field->nonce(),
            $key
        );

        if ($plaintext === false) {
            throw CryptoException::tampered('valeur chiffrée');
        }

        return $plaintext;
    }

    public function assertKeyLength(string $key): void
    {
        if (strlen($key) !== self::KEY_BYTES) {
            throw CryptoException::invalidKeyLength(self::KEY_BYTES, strlen($key));
        }
    }
}
