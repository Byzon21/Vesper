<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Supabase — URL publique + chemins Vault pour les secrets
    |--------------------------------------------------------------------------
    | Les clés (anon, service_role) ne sont JAMAIS ici : elles sont résolues
    | à la volée via le Vault (chemins ci-dessous). L'URL et le JWKS sont
    | publics (non secrets).
    */
    'url' => env('SUPABASE_URL'),
    'jwks_url' => env('SUPABASE_JWKS_URL'),
    'issuer' => env('SUPABASE_JWT_ISSUER'),
    'audience' => env('SUPABASE_JWT_AUDIENCE', 'authenticated'),
    // Tolérance d'horloge (secondes) pour la vérification JWT (décalage client/serveur).
    'jwt_leeway' => env('SUPABASE_JWT_LEEWAY', 120),

    // Clés en clair via env (DEV uniquement) ; en production elles sont
    // résolues via le Vault (vault_paths ci-dessous).
    'anon_key' => env('SUPABASE_ANON_KEY'),
    'service_role_key' => env('SUPABASE_SERVICE_ROLE_KEY'),

    'vault_paths' => [
        'anon_key' => 'vesper/supabase/anon_key',
        'service_role_key' => 'vesper/supabase/service_role_key',
    ],

];
