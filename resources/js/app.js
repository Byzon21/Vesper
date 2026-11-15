/**
 * Vesper — Point d'entrée frontend (ultra-léger).
 * Alpine.js (interactivité) + thème + analytics UX + enregistrement PWA.
 * Livewire est chargé côté Blade (scripts avec nonce CSP).
 */
import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/sora/600.css';
import '@fontsource/sora/700.css';
import '@fontsource/sora/800.css';

import Alpine from 'alpinejs';

import './theme';
import './posthog';
import './supabase';
import './auth';
import './pwa/register';
import { initGlobe } from './globe';
import { initCharts } from './charts';

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

// Délégation des actions (CSP-safe : remplace les handlers onclick inline,
// qui sont bloqués par la Content-Security-Policy).
document.addEventListener('click', (event) => {
    const el = event.target.closest('[data-action]');
    if (!el) return;
    const fn = window[el.dataset.action];
    if (typeof fn === 'function') fn(event);
});

// Révélation au scroll (animations d'apparition progressive).
// La classe .js est posée tôt par un script inline (nonce CSP) dans <head>
// pour éviter tout flash ; ici on ne fait qu'observer les éléments [data-reveal].
document.documentElement.classList.add('js');

if ('IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
    );

    document.querySelectorAll('[data-reveal]').forEach((el) => revealObserver.observe(el));
}

// Globe terrestre (héros de la landing).
const globeCanvas = document.getElementById('globe-canvas');
if (globeCanvas) {
    initGlobe(globeCanvas);
}

// Graphiques (Chart.js).
initCharts();
document.addEventListener('livewire:navigated', initCharts);

