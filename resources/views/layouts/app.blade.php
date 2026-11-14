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
    <meta name="vesper-user" content="{{ auth()->check() ? auth()->id() : '' }}">

    <title>{{ $title ?? __('app.name') }} — Vesper</title>

    {{-- PWA --}}
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">

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

    <main class="mx-auto min-h-screen w-full max-w-lg px-4 pb-24 pt-6">
        {{ $slot ?? '' }}
    </main>

    <x-navigation.bottom-nav />

    {{-- Champ honeypot anti-bot (invisible pour les humains) --}}
    <div style="position:absolute;left:-9999px" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off">
    </div>

    @livewireScripts(['nonce' => $cspNonce ?? null])
</body>
</html>
