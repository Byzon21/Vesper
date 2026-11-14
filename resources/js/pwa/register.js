/**
 * Vesper — Enregistrement du Service Worker (PWA installable, offline-first
 * pour les assets statiques uniquement ; AUCUNE donnée sensible en cache).
 */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((err) => {
            // Échec silencieux : ne jamais bloquer l'expérience.
            console.warn('SW registration failed', err);
        });
    });
}
