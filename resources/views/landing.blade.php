<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="Vesper — Gestion de dépenses personnelles et de couple, ultra-sécurisée.">
    <title>Vesper — Gestion de dépenses personnelles et de couple</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">

    <header class="mx-auto flex max-w-5xl items-center justify-between px-4 py-6">
        <div class="text-xl font-bold">Vesper</div>
        <nav class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="btn-ghost">Se connecter</a>
            <a href="{{ route('register') }}" class="btn-primary">Essai gratuit 7 jours</a>
        </nav>
    </header>

    <section class="mx-auto max-w-3xl px-4 py-16 text-center">
        <h1 class="text-4xl font-bold leading-tight">Vos finances personnelles et de couple, en toute sécurité</h1>
        <p class="mt-4 text-lg text-slate-500">
            Vesper vous aide à suivre vos dépenses, à gérer plusieurs devises (USD, CDF, CFA…) et à
            comprendre vos habitudes — avec une sécurité de niveau bancaire.
        </p>
        <div class="mt-8 flex items-center justify-center gap-3">
            <a href="{{ route('register') }}" class="btn-primary">Commencer l'essai gratuit</a>
            <a href="{{ route('login') }}" class="btn-ghost">J'ai déjà un compte</a>
        </div>
    </section>

    <section class="mx-auto grid max-w-5xl grid-cols-1 gap-6 px-4 py-16 md:grid-cols-3">
        <div class="card">
            <h3 class="font-semibold">Multi-devises (110+)</h3>
            <p class="mt-2 text-sm text-slate-500">USD, Franc congolais (CDF), CFA, et plus. Gestion bimétaire USD/CDF pour la RDC.</p>
        </div>
        <div class="card">
            <h3 class="font-semibold">Mode couple</h3>
            <p class="mt-2 text-sm text-slate-500">Partagez un foyer, suivez vos dépenses communes et personnelles.</p>
        </div>
        <div class="card">
            <h3 class="font-semibold">Chiffrement de bout en bout</h3>
            <p class="mt-2 text-sm text-slate-500">AES-256-GCM, hiérarchie de clés, Zero Trust. Même nous ne pouvons pas lire vos montants.</p>
        </div>
        <div class="card">
            <h3 class="font-semibold">Rapports PDF</h3>
            <p class="mt-2 text-sm text-slate-500">Générez des rapports financiers professionnels, par catégorie ou par période.</p>
        </div>
        <div class="card">
            <h3 class="font-semibold">Analyses intelligentes</h3>
            <p class="mt-2 text-sm text-slate-500">Prévisions de fin de mois, alertes de budget, catégorisation automatique.</p>
        </div>
        <div class="card">
            <h3 class="font-semibold">Mobile-first</h3>
            <p class="mt-2 text-sm text-slate-500">Une PWA rapide et installable sur Android et iOS.</p>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-16 text-center">
        <h2 class="text-2xl font-bold">Essayez gratuitement pendant 7 jours</h2>
        <p class="mt-2 text-slate-500">Aucune carte bancaire requise. Toutes les fonctionnalités incluses.</p>
        <a href="{{ route('register') }}" class="btn-primary mt-6">Créer mon compte</a>
    </section>

    <footer class="py-8 text-center text-sm text-slate-400">© 2026 Vesper — Vos données sont chiffrées de bout en bout.</footer>

</body>
</html>
