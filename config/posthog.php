<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | PostHog — Analytics produit
    |--------------------------------------------------------------------------
    | Deux niveaux de clés :
    |  - Clé PROJET publique (phc_)   : utilisée côté client (JS) et pour la
    |    capture serveur. Conçue pour être publique, mais jamais hardcodée.
    |  - Clé PERSONNELLE (phx_)       : SECRET strict (admin API). Résolue
    |    via le Vault uniquement.
    */
    'enabled' => (bool) env('POSTHOG_ENABLED', true),
    'host' => env('POSTHOG_HOST', 'https://us.i.posthog.com'),
    'project_token' => env('POSTHOG_PROJECT_TOKEN'),
    'personal_api_key' => env('POSTHOG_PERSONAL_API_KEY'),
    'personal_key_vault' => env('POSTHOG_PERSONAL_KEY_VAULT', 'vesper/posthog/personal_api_key'),

];
