<?php

declare(strict_types=1);

namespace App\Domain\Analytics;

use App\Services\Supabase\SupabaseClient;

/**
 * Ingestion des événements d'analytics UX.
 *
 * Écrit via Supabase en mode "asUser" : les politiques RLS garantissent que
 * chaque événement est rattaché à son propre profil. L'IP est hachée (jamais
 * stockée en clair) pour la conformité vie privée.
 */
final class UxAnalyticsService
{
    public function __construct(
        private readonly SupabaseClient $supabase,
    ) {
    }

    /**
     * @param list<UxEvent> $events
     */
    public function record(array $events, string $profileId, string $accessToken, string $ipHash): void
    {
        if ($events === []) {
            return;
        }

        $this->supabase->asUser($accessToken);

        foreach ($events as $event) {
            $this->supabase->insert('ux_analytics_events', [
                'profile_id' => $profileId,
                'session_id' => $event->sessionId,
                'event_type' => $event->type->value,
                'page' => mb_substr($event->page, 0, 255),
                'component' => $event->component,
                'element_id' => $event->elementId,
                'metadata' => $event->metadata,
                'client_ts' => $event->clientTs->format(DATE_ATOM),
                'ip_hash' => $ipHash,
            ]);
        }
    }
}
