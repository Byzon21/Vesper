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

    const { data, error } = await supabase.auth.signUp({ email, password });

    if (error) {
        if (feedback) feedback.textContent = error.message;
        return;
    }

    if (data.session) {
        const res = await establishServerSession(data.session.access_token);
        window.location.href = res.ok ? '/dashboard' : '/login';
    } else if (feedback) {
        feedback.textContent = 'Vérifiez votre boîte mail pour confirmer votre inscription (essai de 7 jours).';
    }
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

