/**
 * Vesper — Enregistrement PWA + invite d'installation.
 * Android/Chrome : beforeinstallprompt → bouton « Installer ».
 * iOS Safari : pas de beforeinstallprompt → bannière d'instructions (Partager → Écran d'accueil).
 */

let deferredPrompt = null;

export function initPwa() {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        showBanner('android');
    });

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        hideBanner();
    });

    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (isIos && !standalone) {
        showBanner('ios');
    }
}

window.installPwa = async () => {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    await deferredPrompt.userChoice;
    deferredPrompt = null;
    hideBanner();
};

window.dismissInstall = () => {
    hideBanner();
    localStorage.setItem('vesper.pwa.dismissed', '1');
};

function showBanner(kind) {
    if (localStorage.getItem('vesper.pwa.dismissed') === '1') return;

    const banner = document.getElementById('pwa-install-banner');
    if (!banner) return;

    const android = banner.querySelector('[data-pwa-android]');
    const ios = banner.querySelector('[data-pwa-ios]');

    if (kind === 'ios') {
        android?.classList.add('hidden');
        ios?.classList.remove('hidden');
    } else {
        android?.classList.remove('hidden');
        ios?.classList.add('hidden');
    }

    banner.classList.remove('hidden');
}

function hideBanner() {
    document.getElementById('pwa-install-banner')?.classList.add('hidden');
}

