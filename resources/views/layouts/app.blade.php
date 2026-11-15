<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ theme: 'system' }"
      x-init="theme = localStorage.getItem('vesper.theme') || 'system'; applyTheme(theme)"
      :class="{ 'dark': theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches) }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0f172a" id="theme-color-meta">
    <meta name="description" content="Vesper — Gestion de dépenses personnelles et de couple, ultra-sécurisée.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="vesper-user" content="{{ session('supabase_user_id', '') }}">

    <title>{{ $title ?? __('app.name') }} — Vesper</title>

    {{-- PWA --}}
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    {{-- Thème initial (anti-flash, exécuté avant le paint) --}}
    <script nonce="{{ $cspNonce ?? '' }}">
        (function () {
            try {
                var t = localStorage.getItem('vesper.theme') || 'system';
                var dark = t === 'dark' || (t === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Livewire (injecte ses scripts avec le nonce CSP) --}}
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">

    {{-- Sidebar (desktop) --}}
    <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-200/60 bg-white/70 backdrop-blur-xl dark:border-slate-800/60 dark:bg-slate-900/70 lg:flex">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-6 py-7">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-blue to-brand-green text-white shadow-lg shadow-brand-blue/25">
                <x-icon name="wallet" class="h-5 w-5" />
            </span>
            <span class="text-lg font-extrabold tracking-tight">Vesper</span>
        </a>

        <nav class="flex-1 space-y-1 px-3">
            @php
                $nav = [
                    ['dashboard', 'home', 'Tableau de bord'],
                    ['transactions', 'wallet', 'Transactions'],
                    ['couple', 'users', 'Couple'],
                    ['reports', 'chart', 'Rapports'],
                    ['settings', 'settings', 'Paramètres'],
                ];
            @endphp
            @foreach ($nav as [$route, $icon, $label])
                <a href="{{ route($route) }}"
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs($route) ? 'bg-brand-green/10 text-brand-green' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    <x-icon name="{{ $icon }}" class="h-5 w-5" />
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="space-y-1 px-3 pb-6">
            <button type="button" data-action="toggleTheme" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                <span class="flex h-5 w-5 items-center justify-center">
                    <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A8.5 8.5 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
                    <svg class="hidden h-5 w-5 dark:inline-block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                </span>
                Thème
            </button>
            <button type="button" data-action="signOut" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-brand-pink transition hover:bg-brand-pink/10">
                <x-icon name="arrow-right" class="h-5 w-5 rotate-180" />
                Déconnexion
            </button>
        </div>
    </aside>

    {{-- Contenu principal --}}
    <div class="lg:pl-64">
        <main class="mx-auto min-h-screen w-full max-w-lg px-4 pb-24 pt-6 sm:max-w-2xl lg:max-w-5xl lg:px-10 lg:pb-16 lg:pt-10">
            {{ $slot ?? '' }}
        </main>
    </div>

    @unless (request()->routeIs('onboarding'))
        <x-navigation.bottom-nav />
    @endunless

    {{-- Bannière d'installation PWA (mobile) --}}
    <div id="pwa-install-banner" class="safe-bottom fixed inset-x-0 bottom-16 z-50 hidden px-4 lg:hidden">
        <div class="mx-auto flex max-w-lg items-center gap-3 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-2xl shadow-slate-900/10 backdrop-blur dark:border-slate-700 dark:bg-slate-800/95">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-blue to-brand-green text-white">
                <x-icon name="wallet" class="h-5 w-5" />
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold">Installer Vesper</p>
                <p data-pwa-android class="text-xs text-slate-500">Une expérience plus fluide, comme une app native.</p>
                <p data-pwa-ios class="hidden text-xs text-slate-500">Appuyez sur Partager, puis « Sur l'écran d'accueil ».</p>
            </div>
            <button type="button" data-action="installPwa" data-pwa-android class="btn-primary !px-3 !py-2 text-xs">Installer</button>
            <button type="button" data-action="dismissInstall" class="shrink-0 rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 dark:hover:bg-slate-700" aria-label="Plus tard">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Champ honeypot anti-bot (invisible pour les humains) --}}
    <div style="position:absolute;left:-9999px" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off">
    </div>

    @livewireScripts(['nonce' => $cspNonce ?? null])
</body>
</html>
