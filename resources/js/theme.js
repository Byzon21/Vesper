/**
 * Vesper — Gestion du thème (Jour / Sombre).
 * Préférence mémorisée localement (localStorage) puis synchronisée avec le
 * backend (préférences utilisateur) lors des appels authentifiés.
 */

// Charge le thème au démarrage.
document.addEventListener('alpine:init', () => {
    const stored = localStorage.getItem('vesper.theme') || 'system';
    window.applyTheme?.(stored);
});

// Réagit au changement système quand le mode "system" est actif.
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    const stored = localStorage.getItem('vesper.theme') || 'system';
    if (stored === 'system') {
        window.applyTheme?.('system');
    }
});
