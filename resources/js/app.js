/**
 * Vesper — Point d'entrée frontend (ultra-léger).
 * Alpine.js (interactivité) + thème + analytics UX + enregistrement PWA.
 * Livewire est chargé côté Blade (scripts avec nonce CSP).
 */
import Alpine from 'alpinejs';

import './theme';
import './posthog';
import './pwa/register';

window.Alpine = Alpine;

// Fonction partagée de bascule de thème (utilisée par le layout et les menus).
window.applyTheme = (theme) => {
    const dark = theme === 'dark'
        || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', dark);

    const meta = document.getElementById('theme-color-meta');
    if (meta) meta.setAttribute('content', dark ? '#020617' : '#f8fafc');
};

window.toggleTheme = () => {
    const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem('vesper.theme', next);
    window.applyTheme(next);
};

Alpine.start();
