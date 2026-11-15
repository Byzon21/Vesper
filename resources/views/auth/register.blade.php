<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a2540">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Inscription — Vesper</title>
    <script nonce="{{ $cspNonce ?? '' }}">document.documentElement.classList.add('js');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-50 px-4 py-10">

    <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-emerald-50 via-slate-50 to-amber-50"></div>
    <div class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-emerald-200/50 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-amber-200/50 blur-3xl"></div>

    <div data-reveal class="relative w-full max-w-sm">
        <a href="{{ route('landing') }}" class="mb-6 flex items-center justify-center gap-2.5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-lg shadow-emerald-500/30">
                <x-icon name="wallet" class="h-5 w-5" />
            </span>
            <span class="text-2xl font-extrabold tracking-tight text-slate-900">Vesper</span>
        </a>

        <div class="card !p-8 shadow-xl shadow-slate-900/5">
            <div class="text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-500/30">
                    <x-icon name="sparkles" class="h-6 w-6" />
                </div>
                <h1 class="mt-4 text-2xl font-bold text-slate-900">Créer un compte</h1>
                <p class="mt-1 text-sm text-slate-500">7 jours offerts, sans carte bancaire.</p>
            </div>

            <div class="mt-6 space-y-3">
                <input id="signup-email" type="email" placeholder="Adresse email" class="input" autocomplete="email">
                <div class="relative">
                    <input id="signup-password" type="password" placeholder="Mot de passe" class="input pr-10" autocomplete="new-password">
                    <button type="button" data-action="togglePassword" data-target="signup-password" aria-label="Afficher le mot de passe"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 transition hover:text-slate-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><path d="M2 2l20 20"/></svg>
                    </button>
                </div>
                <ul class="space-y-1 text-xs text-slate-400">
                    <li data-password-check="length">8 caractères minimum</li>
                    <li data-password-check="lower">Une minuscule</li>
                    <li data-password-check="upper">Une majuscule</li>
                    <li data-password-check="digit">Un chiffre</li>
                    <li data-password-check="special">Un caractère spécial</li>
                </ul>
                <p id="signup-feedback" class="text-sm text-red-500"></p>

                <button type="button" data-action="signUpWithEmail" class="btn-primary w-full">
                    <span class="btn-idle inline-flex items-center gap-2">
                        Commencer l'essai
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </span>
                    <span class="btn-loading hidden items-center gap-2">
                        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                        </svg>
                        Envoi de l'email…
                    </span>
                </button>

                <div class="flex items-center gap-3 text-xs text-slate-400">
                    <span class="h-px flex-1 bg-slate-200"></span>
                    ou
                    <span class="h-px flex-1 bg-slate-200"></span>
                </div>

                <button type="button" data-action="signInWithGoogle" class="btn-ghost w-full">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continuer avec Google
                </button>

                <p class="text-center text-sm text-slate-500">
                    Déjà un compte ?
                    <a href="{{ route('login') }}" class="font-semibold text-brand-green transition hover:text-brand-green">Se connecter</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
