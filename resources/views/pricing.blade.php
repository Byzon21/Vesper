<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#022c22">
    <title>Tarifs — Vesper</title>
    <script nonce="{{ $cspNonce ?? '' }}">document.documentElement.classList.add('js');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-50 px-4 py-10">

    <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-emerald-50 via-slate-50 to-amber-50"></div>
    <div class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-emerald-200/50 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-amber-200/50 blur-3xl"></div>

    <div data-reveal class="relative w-full max-w-md">
        <a href="{{ route('landing') }}" class="mb-6 flex items-center justify-center gap-2.5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-lg shadow-emerald-500/30">
                <x-icon name="wallet" class="h-5 w-5" />
            </span>
            <span class="text-2xl font-extrabold tracking-tight text-slate-900">Vesper</span>
        </a>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-500 p-6 text-center text-white">
                <p class="text-sm font-bold uppercase tracking-widest text-emerald-100">Premium</p>
                <p class="mt-2 text-4xl font-extrabold tracking-tight">9,99 €<span class="text-base font-medium text-emerald-100">/mois</span></p>
                <p class="mt-1 text-sm text-emerald-50/90">Votre essai gratuit est terminé</p>
            </div>

            <div class="p-6">
                <ul class="space-y-3">
                    @foreach (['Transactions illimitées', 'Mode couple', 'Multi-devises (110+)', 'Rapports PDF illimités', 'Analyses intelligentes (ML)'] as $feature)
                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                <x-icon name="check" class="h-4 w-4" />
                            </span>
                            <span class="text-sm text-slate-700">{{ $feature }}</span>
                        </li>
                    @endforeach
                </ul>

                <button type="button" class="btn-primary mt-6 w-full">
                    Passer à Premium
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </button>

                <p class="mt-4 text-center text-sm text-slate-400">
                    <a href="{{ route('landing') }}" class="transition hover:text-emerald-600">← Retour à l'accueil</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
