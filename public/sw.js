/**
 * Vesper — Service Worker (PWA offline-first).
 * ⚠️  SÉCURITÉ : ne met en cache QUE les assets statiques (GET, même origine).
 *     AUCUNE réponse d'API (/api/*), aucun token, aucune donnée financière.
 */
const CACHE_NAME = 'vesper-static-v1';
const PRECACHE = [
    '/manifest.webmanifest',
    '/icons/papicons.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Ne jamais intercepter les API (données sensibles), les requêtes non-GET,
    // ni les requêtes cross-origin (PostHog, Unsplash, Supabase…).
    if (request.method !== 'GET' || url.origin !== location.origin) {
        return;
    }
    if (url.pathname.startsWith('/api/')) {
        return;
    }

    // Navigation (HTML) : network-first — toujours frais, pas de cache stale.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    return response;
                })
                .catch(() => caches.match(request)),
        );
        return;
    }

    // Assets statiques (hachés par Vite) : cache-first, sûr.
    event.respondWith(
        caches.match(request).then((cached) => {
            if (cached) return cached;
            return fetch(request).then((response) => {
                if (response.ok) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                }
                return response;
            });
        }),
    );
});
