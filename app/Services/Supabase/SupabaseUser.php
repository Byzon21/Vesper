<?php

declare(strict_types=1);

namespace App\Services\Supabase;

/**
 * Utilisateur Supabase authentifié (claims du JWT vérifié).
 */
final readonly class SupabaseUser
{
    /**
     * @param array<string, mixed> $claims
     */
    public function __construct(
        public string $id,
        public ?string $email,
        public array $claims,
    ) {
    }

    public function emailVerified(): bool
    {
        return (bool) ($this->claims['email_verified'] ?? false);
    }

    public function appMetadata(): array
    {
        return (array) ($this->claims['app_metadata'] ?? []);
    }
}
