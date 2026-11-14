<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Analytics\UxAnalyticsService;
use App\Domain\Analytics\UxEvent;
use App\Domain\Analytics\UxEventType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Point d'ingestion des événements d'analytics UX (POST /api/ux-events).
 *
 * Sécurisé par : authentification session, HMAC (middleware global), rate
 * limiting strict et validation stricte du payload (taille bornée).
 */
final class AnalyticsController extends Controller
{
    public function __invoke(Request $request, UxAnalyticsService $service): JsonResponse
    {
        $validated = $request->validate([
            'events' => ['required', 'array', 'max:50'],
            'events.*.session_id' => ['required', 'uuid'],
            'events.*.event_type' => ['required', 'string', 'in:page_view,click,scroll,focus,error,idle,rage_click,dead_click'],
            'events.*.page' => ['required', 'string', 'max:255'],
            'events.*.component' => ['nullable', 'string', 'max:120'],
            'events.*.element_id' => ['nullable', 'string', 'max:120'],
            'events.*.metadata' => ['nullable', 'array', 'max:20'],
            'events.*.client_ts' => ['required', 'date'],
        ]);

        $events = array_map(
            fn (array $raw): UxEvent => new UxEvent(
                sessionId: $raw['session_id'],
                type: UxEventType::from($raw['event_type']),
                page: $raw['page'],
                component: $raw['component'] ?? null,
                elementId: $raw['element_id'] ?? null,
                metadata: $raw['metadata'] ?? [],
                clientTs: new \DateTimeImmutable($raw['client_ts']),
            ),
            $validated['events'],
        );

        $service->record(
            $events,
            (string) $request->user()->getAuthIdentifier(),
            (string) $request->session()->get('supabase_access_token', ''),
            $this->hashIp($request),
        );

        return response()->json(['status' => 'ok']);
    }

    /**
     * Hachage irréversible de l'IP (sel salé) — jamais d'IP en clair.
     */
    private function hashIp(Request $request): string
    {
        return hash_hmac('sha256', (string) $request->ip(), (string) config('app.key'));
    }
}
