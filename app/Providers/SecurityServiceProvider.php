<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Supabase\SupabaseClient;
use App\Services\Supabase\SupabaseJwtVerifier;
use App\Support\Security\Crypto\FieldCipher;
use App\Support\Security\Crypto\KeyEnvelope;
use App\Support\Security\FieldEncryptor;
use App\Support\Security\KeyHierarchy;
use App\Support\Security\Vault\Drivers\HashiCorpVault;
use App\Support\Security\Vault\Drivers\LocalEncryptedVault;
use App\Support\Security\Vault\SecretVault;
use App\Support\Security\Vault\VaultManager;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Enregistre les services de sécurité (Vault, chiffrement, Supabase).
 *
 * Règles :
 *  - Tous les secrets transitent par le Vault (jamais par la config en clair).
 *  - Les singletons garantissent une clé/un client unique par requête.
 */
final class SecurityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // --- Primitives cryptographiques ---
        $this->app->singleton(FieldCipher::class);

        $this->app->singleton(KeyEnvelope::class, fn ($app) => new KeyEnvelope($app->make(FieldCipher::class)));

        $this->app->singleton(FieldEncryptor::class, fn ($app) => new FieldEncryptor($app->make(FieldCipher::class)));

        // --- Vault ---
        $this->app->singleton(LocalEncryptedVault::class, function ($app) {
            $masterKey = base64_decode((string) config('vault.master_key', ''), true);

            return new LocalEncryptedVault(
                $app->make(FieldCipher::class),
                storage_path('secure/vault.enc'),
                $masterKey === false ? '' : $masterKey,
            );
        });

        $this->app->singleton(HashiCorpVault::class, function ($app) {
            return new HashiCorpVault(
                $app->make(ClientInterface::class),
                (string) config('vault.addr', ''),
                (string) config('vault.role_id', ''),
                (string) config('vault.secret_id', ''),
            );
        });

        $this->app->singleton(VaultManager::class, function ($app) {
            return new VaultManager(
                $app->make(LocalEncryptedVault::class),
                $app->make(HashiCorpVault::class),
                (string) config('vault.driver', 'local'),
            );
        });

        // Interface → Manager (les middlewares typent SecretVault).
        $this->app->alias(VaultManager::class, SecretVault::class);
        $this->app->singleton(SecretVault::class, VaultManager::class);

        $this->app->singleton(KeyHierarchy::class, fn ($app) => new KeyHierarchy(
            $app->make(VaultManager::class),
            $app->make(KeyEnvelope::class),
        ));

        // --- Supabase (BFF : secrets résolus via Vault) ---
        $this->app->singleton(SupabaseJwtVerifier::class, function ($app) {
            return new SupabaseJwtVerifier(
                $app->make(ClientInterface::class),
                (string) config('supabase.jwks_url', ''),
                config('supabase.issuer'),
                config('supabase.audience'),
            );
        });

        $this->app->singleton(SupabaseClient::class, fn ($app) => new SupabaseClient(
            $app->make(ClientInterface::class),
            (string) config('supabase.url', ''),
            $app->make(VaultManager::class),
        ));
    }
}
