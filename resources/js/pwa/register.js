/**
 * Service worker DÉSACTIVÉ en développement : il interfère avec le
 * rechargement des assets (cache stale → "Failed to fetch").
 * À réactiver pour la production (PWA offline).
 */
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then((registrations) => {
        registrations.forEach((registration) => registration.unregister());
    });
}
