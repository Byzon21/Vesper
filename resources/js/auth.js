/**
 * Vesper — Flux d'authentification (Google OAuth + Email / Mot de passe).
 *
 *  1. signInWithGoogle() → redirige vers Google OAuth.
 *  2. signUpWithEmail() → inscription email/mot de passe (essai 7 jours).
 *  3. Retour sur /auth/callback → handleAuthCallback() envoie le JWT au
 *     backend Laravel (/auth/session) qui établit une session chiffrée.
 */
import { supabase } from './supabase';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

async function establishServerSession(accessToken) {
    return fetch('/auth/session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${accessToken}`,
            'X-CSRF-TOKEN': csrfToken(),
        },
    });
}

export async function signInWithGoogle() {
    if (!supabase) return;

    await supabase.auth.signInWithOAuth({
        provider: 'google',
        options: {
            redirectTo: `${window.location.origin}/auth/callback`,
        },
    });
}

export async function signUpWithEmail() {
    if (!supabase) return;

    const email = document.getElementById('signup-email')?.value?.trim();
    const password = document.getElementById('signup-password')?.value;
    const feedback = document.getElementById('signup-feedback');

    if (!email || !password) {
        if (feedback) feedback.textContent = 'Veuillez renseigner votre email et un mot de passe.';
        return;
    }

    const failed = validatePassword(password).filter((c) => !c.ok);
    if (failed.length > 0) {
        if (feedback) feedback.textContent = 'Mot de passe trop faible : ' + failed.map((c) => c.label).join(', ') + '.';
        return;
    }

    const { data, error } = await supabase.auth.signUp({
        email,
        password,
        options: {
            // Après clic sur le lien de confirmation, on atterrit sur /auth/callback
            // (qui établit la session puis redirige vers le dashboard).
            emailRedirectTo: `${window.location.origin}/auth/callback`,
        },
    });

    if (error) {
        if (feedback) feedback.textContent = error.message;
        return;
    }

    // Cas 1 : session immédiate (confirmation d'email désactivée côté Supabase).
    if (data.session) {
        const res = await establishServerSession(data.session.access_token);
        window.location.href = res.ok ? '/dashboard' : '/login';
        return;
    }

    // Cas 2 : confirmation d'email requise → on la confirme côté serveur
    // (essai gratuit instantané), puis on reconnecte l'utilisateur.
    if (data.user) {
        const confirmRes = await fetch('/auth/confirm', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ user_id: data.user.id }),
        });

        if (confirmRes.ok) {
            const { data: signedIn, error: signInError } = await supabase.auth.signInWithPassword({ email, password });

            if (signInError) {
                if (feedback) feedback.textContent = signInError.message;
                return;
            }

            const res = await establishServerSession(signedIn.session.access_token);
            window.location.href = res.ok ? '/dashboard' : '/login';
            return;
        }
    }

    if (feedback) feedback.textContent = 'Vérifiez votre boîte mail pour confirmer votre inscription (essai de 7 jours).';
}

export async function handleAuthCallback() {
    if (!supabase) {
        window.location.href = '/login';
        return;
    }

    const { data: { session } } = await supabase.auth.getSession();

    if (!session) {
        window.location.href = '/login';
        return;
    }

    const res = await establishServerSession(session.access_token);

    window.location.href = res.ok ? '/dashboard' : '/login';
}

export async function signOut() {
    if (supabase) {
        await supabase.auth.signOut();
    }

    await fetch('/auth/logout', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken() },
    });

    window.location.href = '/';
}

// Exposés aux vues Blade (délégation data-action).
window.signInWithGoogle = signInWithGoogle;
window.signUpWithEmail = signUpWithEmail;
window.handleAuthCallback = handleAuthCallback;
window.signOut = signOut;

// --- Robustesse du mot de passe ---

function validatePassword(password) {
    return [
        { key: 'length', ok: password.length >= 8, label: '8 caractères minimum' },
        { key: 'lower', ok: /[a-z]/.test(password), label: 'une minuscule' },
        { key: 'upper', ok: /[A-Z]/.test(password), label: 'une majuscule' },
        { key: 'digit', ok: /[0-9]/.test(password), label: 'un chiffre' },
        { key: 'special', ok: /[^A-Za-z0-9]/.test(password), label: 'un caractère spécial' },
    ];
}

function updatePasswordChecks(password) {
    const checks = validatePassword(password);
    document.querySelectorAll('[data-password-check]').forEach((item) => {
        const key = item.dataset.passwordCheck;
        const ok = checks.find((c) => c.key === key)?.ok ?? false;
        item.classList.toggle('text-brand-green', ok);
        item.classList.toggle('text-slate-400', !ok);
    });
}

// Affiche / masque le mot de passe (data-action="togglePassword").
export function togglePassword(event) {
    const btn = event.target.closest('[data-action="togglePassword"]');
    if (!btn) return;
    const input = document.getElementById(btn.dataset.target);
    if (!input) return;
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.querySelectorAll('svg').forEach((icon) => icon.classList.toggle('hidden'));
    btn.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
    input.focus();
}
window.togglePassword = togglePassword;

// Checklist de mot de passe en direct.
const passwordInput = document.getElementById('signup-password');
if (passwordInput) {
    passwordInput.addEventListener('input', (e) => updatePasswordChecks(e.target.value));
}

// Sur la page de callback (/auth/callback), on lance automatiquement la
// récupération de session. Le module est chargé en différé (type="module"),
// donc document.body est déjà disponible : pas de course avec un script inline.
if (document.body && document.body.dataset.callback === 'auth') {
    handleAuthCallback();
}

