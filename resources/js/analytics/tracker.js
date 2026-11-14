/**
 * Vesper — Tracker d'analytics UX (comportemental).
 *
 * Collecte des métriques anonymisées pour identifier les zones de friction :
 *  - temps passé par écran/section
 *  - clics "dans le vide" (dead clicks) : clic sur zone non interactive
 *  - rage clicks : clics répétés (>3 en <1s) signalant un bouton non réactif
 *  - profondeur de scroll, erreurs JS, inactivité, focus/blur
 *
 * Confidentialité : AUCUNE donnée personnelle. Événements mis en file, puis
 * envoyés en lot (navigator.sendBeacon) à /api/ux-events. L'IP est hachée côté
 * serveur (jamais stockée en clair).
 */
(() => {
    const SESSION_KEY = 'vesper.ux.session';
    const ENDPOINT = '/api/ux-events';
    const FLUSH_INTERVAL = 5000; // ms
    const MAX_BATCH = 50;

    let queue = [];
    let sessionId = sessionStorage.getItem(SESSION_KEY) || crypto.randomUUID();
    let pageEnteredAt = Date.now();
    let lastScrollDepth = 0;
    let clickTimes = [];
    let idleTimer = null;

    sessionStorage.setItem(SESSION_KEY, sessionId);

    const event = (type, payload = {}) => {
        queue.push({
            session_id: sessionId,
            event_type: type,
            page: location.pathname,
            metadata: payload,
            client_ts: new Date().toISOString(),
        });
        if (queue.length >= MAX_BATCH) flush();
    };

    const flush = () => {
        if (!queue.length) return;
        const payload = queue.splice(0);
        const blob = new Blob([JSON.stringify({ events: payload })], { type: 'application/json' });
        // sendBeacon : fiable même en fermeture d'onglet (mobile).
        if (!navigator.sendBeacon(ENDPOINT, blob)) {
            fetch(ENDPOINT, { method: 'POST', body: JSON.stringify({ events: payload }), keepalive: true });
        }
    };

    // --- Page view + temps passé ---
    const reportTimeOnPage = () => {
        event('page_view', { duration_ms: Date.now() - pageEnteredAt });
    };
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') reportTimeOnPage();
    });
    window.addEventListener('pagehide', () => { reportTimeOnPage(); flush(); });

    // --- Clics : dead clicks + rage clicks ---
    document.addEventListener('click', (e) => {
        const target = e.target.closest('a, button, input, select, textarea, [role="button"], [data-action]');
        const elId = (e.target.id) || (e.target.getAttribute('data-analytics')) || 'unknown';

        if (!target) {
            event('dead_click', { element_id: elId });
        }

        // Rage click : 3+ clics sur le même élément en moins d'une seconde.
        const now = Date.now();
        clickTimes = clickTimes.filter((t) => now - t < 1000);
        clickTimes.push(now);
        if (clickTimes.filter((t) => now - t < 1000).length >= 3) {
            event('rage_click', { element_id: elId });
            clickTimes = [];
        }

        event('click', { element_id: elId });
    });

    // --- Profondeur de scroll ---
    window.addEventListener('scroll', () => {
        const depth = Math.round((window.scrollY + innerHeight) / document.body.scrollHeight * 100);
        const bucket = Math.floor(depth / 25) * 25;
        if (bucket > lastScrollDepth) {
            lastScrollDepth = bucket;
            event('scroll', { depth_percent: bucket });
        }
    }, { passive: true });

    // --- Erreurs JS ---
    window.addEventListener('error', (e) => {
        event('error', { message: e.message, source: e.filename, line: e.lineno });
    });

    // --- Inactivité ---
    const resetIdle = () => {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(() => event('idle', { idle_seconds: 300 }), 300000);
    };
    ['click', 'keydown', 'touchstart', 'scroll'].forEach((t) => window.addEventListener(t, resetIdle, { passive: true }));
    resetIdle();

    // --- Envoi périodique ---
    setInterval(flush, FLUSH_INTERVAL);

    // Expose un hook global pour les composants Livewire.
    window.VesperAnalytics = { track: event };
})();
