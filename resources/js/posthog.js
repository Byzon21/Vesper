/**
 * Vesper — PostHog (analytics produit).
 *
 * PostHog remplace le tracker custom : il capture nativement
 *  - les vues de page et le temps par écran (capture_pageleave),
 *  - les clics (autocapture) dont les dead clicks et rage clicks,
 *  - les enregistrements de session (replay) pour détecter les frictions.
 *
 * Confidentialité (app financière) :
 *  - texte/inputs masqués dans les replays (maskAllText / maskAllInputs),
 *  - identification par UUID Supabase (opaque, jamais d'e-mail/PII).
 */
import posthog from 'posthog-js';

const key = import.meta.env.VITE_POSTHOG_KEY;
const host = import.meta.env.VITE_POSTHOG_HOST || 'https://us.i.posthog.com';

if (key) {
    posthog.init(key, {
        api_host: host,
        autocapture: true,
        capture_pageview: true,
        capture_pageleave: true,
        persistence: 'localStorage+cookie',
        session_recording: {
            maskAllText: true,
            maskAllInputs: true,
            maskAllElementAttributes: true,
        },
        loaded: (ph) => {
            // Identification par ID opaque (UUID Supabase), injecté côté serveur.
            const userId = document.querySelector('meta[name="vesper-user"]')?.content;
            if (userId) {
                ph.identify(userId, { app: 'vesper' });
            }
        },
    });
}

window.posthog = posthog;

export default posthog;
