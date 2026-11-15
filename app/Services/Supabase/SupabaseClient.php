<?php

declare(strict_types=1);

namespace App\Services\Supabase;

use App\Support\Security\Vault\SecretVault;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Client serveur Supabase (PostgREST + Storage).
 *
 * Deux modes d'accès, garantissant l'isolation par RLS :
 *
 *  - asUser(jeton)  : chaque requête porte "Authorization: Bearer {jeton_user}",
 *    donc PostgreSQL applique les politiques RLS → l'utilisateur ne voit QUE
 *    ses données (ou celles de son foyer). Le jeton utilisateur ne quitte
 *    jamais le serveur.
 *
 *  - asService()    : utilise la clé service_role (récupérée dans le Vault,
 *    JAMAIS exposée au client) pour les opérations d'administration strictement
 *    contrôlées (provisionnement de profil à l'inscription, jobs planifiés).
 */
final class SupabaseClient
{
    private string $authorization;

    public function __construct(
        private readonly ClientInterface $http,
        private readonly string $url,
        private readonly SecretVault $vault,
    ) {
        // Par défaut : clé anon (lecture publique soumise à RLS).
        $this->authorization = $this->anonKey();
    }

    public function asUser(string $userAccessToken): self
    {
        $this->authorization = 'Bearer ' . $userAccessToken;

        return $this;
    }

    public function asService(): self
    {
        $this->authorization = 'Bearer ' . $this->serviceRoleKey();

        return $this;
    }

    /**
     * SELECT via PostgREST.
     *
     * @param array<string, mixed> $query paramètres de requête (select, eq, in, order…)
     * @return array<int, array<string, mixed>>
     */
    public function select(string $table, array $query = []): array
    {
        return $this->request('GET', "/rest/v1/{$table}", ['query' => $query]);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    public function insert(string $table, array $row): ?array
    {
        return $this->request('POST', "/rest/v1/{$table}", ['json' => $row])[0] ?? null;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    public function update(string $table, array $row, array $query = []): ?array
    {
        return $this->request('PATCH', "/rest/v1/{$table}", [
            'json' => $row,
            'query' => $query,
        ])[0] ?? null;
    }

    public function delete(string $table, array $query = []): void
    {
        $this->request('DELETE', "/rest/v1/{$table}", ['query' => $query]);
    }

    public function rpc(string $function, array $params = []): array
    {
        return $this->request('POST', "/rest/v1/rpc/{$function}", ['json' => $params]);
    }

    /**
     * Confirme l'adresse email d'un utilisateur via l'API Admin Auth.
     *
     * Utilisé pour l'inscription instantanée (essai gratuit) : quand Supabase
     * exige une confirmation d'email, `signUp()` renvoie l'utilisateur SANS
     * session. On confirme alors l'email côté serveur (clé service_role), puis
     * le client se reconnecte immédiatement — sans attendre un email qui, sur
     * le service email par défaut de Supabase, n'arrive pas toujours.
     *
     * @param string $userId identifiant UUID Supabase de l'utilisateur
     */
    public function confirmUserEmail(string $userId): bool
    {
        $key = $this->serviceRoleKey();

        try {
            $this->http->request('PUT', rtrim($this->url, '/') . "/auth/v1/admin/users/{$userId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $key,
                    'apikey' => $key,
                    'Content-Type' => 'application/json',
                ],
                'json' => ['email_confirm' => true],
            ]);

            return true;
        } catch (GuzzleException) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $options
     * @return array<int|string, mixed>
     */
    private function request(string $method, string $path, array $options = []): array
    {
        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => $this->authorization,
            'apikey' => $this->anonKey(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]);

        try {
            $response = $this->http->request($method, rtrim($this->url, '/') . $path, $options);
            $body = (string) $response->getBody();

            if ($body === '' || $body === 'null') {
                return [];
            }

            return (array) json_decode($body, true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Erreur Supabase.', 0, $e);
        }
    }

    private function anonKey(): string
    {
        $envKey = (string) config('supabase.anon_key', '');

        if ($envKey !== '') {
            return $envKey;
        }

        return $this->vault->resolve((string) config('supabase.vault_paths.anon_key'))->reveal();
    }

    /**
     * Clé service_role (bypass RLS) — env en DEV, Vault en production.
     */
    private function serviceRoleKey(): string
    {
        $envKey = (string) config('supabase.service_role_key', '');

        if ($envKey !== '') {
            return $envKey;
        }

        return $this->vault->resolve((string) config('supabase.vault_paths.service_role_key'))->reveal();
    }
}
