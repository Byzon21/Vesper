<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Gestionnaire de secrets (Vault)
    |--------------------------------------------------------------------------
    | driver     : "local" (fichier chiffré dev) | "hashicorp" (production)
    | master_key : clé maîtresse locale (base64, 32 octets) — DEV uniquement,
    |              en production les secrets viennent de HashiCorp Vault/KMS.
    */
    'driver' => env('VAULT_DRIVER', 'local'),
    'master_key' => env('VAULT_MASTER_KEY'),
    'addr' => env('VAULT_ADDR'),
    'role_id' => env('VAULT_APPROLE_ROLE_ID'),
    'secret_id' => env('VAULT_APPROLE_SECRET_ID'),
    'token_ttl' => (int) env('VAULT_TOKEN_TTL', 300),

];
