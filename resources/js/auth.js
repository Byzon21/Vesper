/**
 * Vesper — Flux d'authentification Google (via Supabase Auth).
 *
 *  1. signInWithGoogle() → redirige vers Google OAuth.
 *  2. Retour sur /auth/callback → handleAuthCallback() récupère le JWT
 *     Supabase et l'envoie au backend Laravel (/auth/session).
 *  3. Laravel vérifie le JWT (JWKS) et établit une session chiffrée.
 */
import { supabase } from './supabase';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
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

    const res = await fetch('/auth/session', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${session.access_token}`,
            'X-CSRF-TOKEN': csrfToken(),
        },
    });

    window.location.href = res.ok ? '/' : '/login';
}

export async function signOut() {
    if (supabase) {
        await supabase.auth.signOut();
    }

    await fetch('/auth/logout', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken() },
    });

    window.location.href = '/login';
}

// Exposés aux vues Blade (attributs onclick).
window.signInWithGoogle = signInWithGoogle;
window.handleAuthCallback = handleAuthCallback;
window.signOut = signOut;
