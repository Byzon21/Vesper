<?php

declare(strict_types=1);

namespace App\Services\Supabase;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Vérification des jetons d'accès Supabase (JWT RS256) via JWKS.
 *
 * Le frontend transmet son jeton Supabase (obtenu après Google OAuth). Le
 * backend vérifie sa signature cryptographique (JWKS officiel Supabase),
 * son émetteur (iss) et son audience (aud) avant d'établir une session
 * serveur chiffrée. Aucune confiance aveugle dans un identifiant client.
 */
final class SupabaseJwtVerifier
{
    /** @var array<string, mixed>|null */
    private ?array $jwksCache = null;

    public function __construct(
        private readonly ClientInterface $http,
        private readonly string $jwksUrl,
        private readonly ?string $issuer = null,
        private readonly ?string $audience = null,
    ) {
    }

    public function verify(string $jwt): SupabaseUser
    {
        try {
            $payload = JWT::decode($jwt, JWK::parseKeySet($this->jwks()));
        } catch (\Throwable $e) {
            throw new \RuntimeException('Jeton Supabase invalide.', 0, $e);
        }

        if ($this->issuer !== null && ($payload->iss ?? null) !== $this->issuer) {
            throw new \RuntimeException('Émetteur du jeton invalide.');
        }

        if ($this->audience !== null && ($payload->aud ?? null) !== $this->audience) {
            throw new \RuntimeException('Audience du jeton invalide.');
        }

        if (empty($payload->sub)) {
            throw new \RuntimeException('Jeton sans identifiant utilisateur.');
        }

        return new SupabaseUser(
            (string) $payload->sub,
            isset($payload->email) ? (string) $payload->email : null,
            (array) $payload,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function jwks(): array
    {
        if ($this->jwksCache !== null) {
            return $this->jwksCache;
        }

        try {
            $response = $this->http->request('GET', $this->jwksUrl);
            $decoded = json_decode((string) $response->getBody(), true);

            if (! is_array($decoded) || ! isset($decoded['keys'])) {
                throw new \RuntimeException('Réponse JWKS invalide.');
            }

            return $this->jwksCache = $decoded;
        } catch (GuzzleException $e) {
            throw new \RuntimeException('JWKS Supabase injoignable.', 0, $e);
        }
    }
}
