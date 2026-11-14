<?php

declare(strict_types=1);

namespace App\Support\Security\RequestIntegrity;

/**
 * Signature HMAC des requêtes Frontend → Backend (anti-phishing / anti-bot).
 *
 * Chaque requête mutante de la PWA officielle est signée avec une clé partagée
 * (stockée dans le Vault, jamais côté client). Un script automatisé ou une page
 * de phishing imitant l'interface ne peut pas produire une signature valide.
 *
 * La signature couvre : méthode, chemin, hash du corps et horodatage. La fenêtre
 * de tolérance (±300 s) empêche la relecture (replay) de requêtes interceptées.
 */
final class HmacSigner
{
    private const ALGO = 'sha256';

    /**
     * @param string $secret clé de signature partagée (32+ octets, issue du Vault)
     */
    public function __construct(
        private readonly string $secret,
    ) {
    }

    /**
     * Génère la signature d'une requête.
     */
    public function sign(string $method, string $path, string $body, int $timestamp): string
    {
        return hash_hmac(self::ALGO, $this->canonicalize($method, $path, $body, $timestamp), $this->secret);
    }

    /**
     * Vérifie la signature fournie, en temps constant, avec protection anti-rejeu.
     */
    public function verify(
        string $providedSignature,
        string $method,
        string $path,
        string $body,
        int $timestamp,
        int $toleranceSeconds = 300,
    ): bool {
        if (abs(time() - $timestamp) > $toleranceSeconds) {
            return false; // requête rejouée ou horloge décalée
        }

        $expected = $this->sign($method, $path, $body, $timestamp);

        return hash_equals($expected, $providedSignature);
    }

    private function canonicalize(string $method, string $path, string $body, int $timestamp): string
    {
        return implode("\n", [
            strtoupper($method),
            $path,
            hash(self::ALGO, $body),
            (string) $timestamp,
        ]);
    }
}
