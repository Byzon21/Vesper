<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use PostHog\PostHog;

/**
 * Capture d'événements côté serveur via PostHog (posthog-php).
 *
 * Utilisée pour les événements métier fiables (transaction créée, rapport
 * généré, dépassement de budget) qui ne peuvent pas être capturés côté client.
 *
 * Le `distinctId` DOIT correspondre à celui du frontend (posthog.identify) :
 * on utilise l'UUID Supabase. Aucune donnée personnelle (PII) dans les props.
 *
 * Nommage recommandé : "[objet] [verbe]" (ex: "transaction created").
 */
final class PostHogService
{
    private bool $ready = false;

    public function __construct()
    {
        $token = (string) config('posthog.project_token', '');

        if ($token !== '' && config('posthog.enabled', true)) {
            PostHog::init($token, [
                'host' => (string) config('posthog.host', 'https://us.i.posthog.com'),
            ]);
            $this->ready = true;
        }
    }

    /**
     * @param array<string, mixed> $properties
     */
    public function capture(string $event, string $distinctId, array $properties = []): void
    {
        if (! $this->ready) {
            return;
        }

        PostHog::capture([
            'distinctId' => $distinctId,
            'event' => $event,
            'properties' => $properties,
        ]);
    }

    /**
     * @param array<string, mixed> $properties
     */
    public function identify(string $distinctId, array $properties = []): void
    {
        if (! $this->ready) {
            return;
        }

        PostHog::identify([
            'distinctId' => $distinctId,
            'properties' => $properties,
        ]);
    }

    /**
     * Vide la file d'événements (à appeler en fin de worker/script long).
     */
    public function flush(): void
    {
        if ($this->ready) {
            PostHog::flush();
        }
    }
}
