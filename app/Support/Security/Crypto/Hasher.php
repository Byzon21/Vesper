<?php

declare(strict_types=1);

namespace App\Support\Security\Crypto;

/**
 * Hachage sécurisé des mots de passe et des jetons.
 *
 *  - Mots de passe      : Argon2id (recommandé OWASP), via password_hash().
 *  - Jetons / clés API  : SHA-256 avec sel unique (hachage irréversible),
 *    empêchant l'exploitation directe en cas de vol de table. La vérification
 *    se fait en temps constant (hash_equals) contre les attaques temporelles.
 */
final class Hasher
{
    private const ARGON2_MEMORY_COST = 65536; // 64 MiB
    private const ARGON2_TIME_COST = 4;
    private const ARGON2_THREADS = 1;

    /**
     * Hache un mot de passe avec Argon2id.
     */
    public function password(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_ARGON2ID, [
            'memory_cost' => self::ARGON2_MEMORY_COST,
            'time_cost' => self::ARGON2_TIME_COST,
            'threads' => self::ARGON2_THREADS,
        ]);
    }

    /**
     * Vérifie un mot de passe en temps constant.
     */
    public function verifyPassword(string $plainPassword, string $hash): bool
    {
        return password_verify($plainPassword, $hash);
    }

    /**
     * Indique si un hachage doit être re-calculé (suite à une évolution des
     * paramètres de sécurité).
     */
    public function passwordNeedsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
            'memory_cost' => self::ARGON2_MEMORY_COST,
            'time_cost' => self::ARGON2_TIME_COST,
            'threads' => self::ARGON2_THREADS,
        ]);
    }

    /**
     * Hache un jeton (clé API, refresh token) de façon irréversible.
     * Format de stockage : "<sel_b64>$<sha256(salt || token)>".
     */
    public function token(string $plainToken): string
    {
        $salt = random_bytes(16);

        return base64_encode($salt) . '$' . hash('sha256', $salt . $plainToken);
    }

    /**
     * Vérifie un jeton en temps constant.
     */
    public function verifyToken(string $plainToken, string $stored): bool
    {
        $parts = explode('$', $stored, 2);

        if (count($parts) !== 2) {
            return false;
        }

        $salt = base64_decode($parts[0], true);
        $hash = $parts[1];

        if ($salt === false || $hash === '') {
            return false;
        }

        return hash_equals($hash, hash('sha256', $salt . $plainToken));
    }
}
