<?php

declare(strict_types=1);

namespace App\Support\Security;

use App\Support\Security\Crypto\KeyEnvelope;
use App\Support\Security\Crypto\SensitiveString;
use App\Support\Security\Vault\SecretVault;
use App\Support\Security\Vault\VaultException;

/**
 * Orchestration de la hiérarchie de clés (Zero Trust) :
 *
 *   Vault Master Key (VMK, hors application)
 *        └─ stocke (chiffré au repos) →
 *   Key Encryption Key (KEK) — une par utilisateur, DANS LE VAULT uniquement
 *        └─ enveloppe →
 *   Data Encryption Key (DEK) — une par utilisateur + une par foyer, EN BASE
 *        └─ chiffre →
 *   Champs sensibles (montants, notes, soldes)
 *
 * Conséquences :
 *  - Une fuite de la base seule est inexploitable (DEK enveloppées par KEK).
 *  - Une fuite du Vault seule est inexploitable (KEK sans les DEK/ciphertexts).
 *  - L'isolation par utilisateur/couple est totale : chaque entité a sa DEK.
 */
final class KeyHierarchy
{
    public function __construct(
        private readonly SecretVault $vault,
        private readonly KeyEnvelope $envelope,
    ) {
    }

    /**
     * Récupère (ou provisionne) la KEK d'un utilisateur.
     * La KEK ne vit QUE dans le Vault ; elle est retournée en mémoire éphémère.
     */
    public function userKek(string $userId): SensitiveString
    {
        $path = "vesper/keys/users/{$userId}/kek";

        try {
            $raw = base64_decode($this->vault->resolve($path)->reveal(), true);

            if ($raw === false || strlen($raw) !== KeyEnvelope::KEY_BYTES) {
                throw VaultException::unresolved($path);
            }

            return new SensitiveString($raw);
        } catch (VaultException) {
            $kek = $this->envelope->generateKek();
            $this->vault->store($path, base64_encode($kek));

            return new SensitiveString($kek);
        }
    }

    /**
     * Génère une nouvelle DEK (clé de chiffrement des données).
     */
    public function generateDek(): SensitiveString
    {
        return new SensitiveString($this->envelope->generateDek());
    }

    /**
     * Enveloppe une DEK avec une KEK pour stockage en base.
     *
     * @param string $context ex: "household:{id}" ou "user:{id}" (AAD anti-substitution)
     */
    public function wrapDek(SensitiveString $dek, SensitiveString $kek, string $context): string
    {
        return $this->envelope->wrap($dek->reveal(), $kek->reveal(), $context);
    }

    /**
     * Désenveloppe une DEK stockée en base avec la KEK en mémoire.
     */
    public function unwrapDek(string $wrappedDek, SensitiveString $kek, string $context): SensitiveString
    {
        return new SensitiveString($this->envelope->unwrap($wrappedDek, $kek->reveal(), $context));
    }
}
