<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Tarifs — Vesper</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4 dark:bg-slate-950">
    <div class="w-full max-w-md text-center">
        <h1 class="text-2xl font-bold">Votre essai gratuit est terminé</h1>
        <p class="mt-2 text-slate-500">Passez à la version Premium pour continuer à utiliser Vesper.</p>

        <div class="card mt-6">
            <h2 class="text-lg font-semibold">Premium</h2>
            <p class="mt-2 text-3xl font-bold">9,99 €<span class="text-sm font-normal text-slate-500">/mois</span></p>
            <ul class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                <li>✓ Transactions illimitées</li>
                <li>✓ Mode couple</li>
                <li>✓ Multi-devises (110+)</li>
                <li>✓ Rapports PDF illimités</li>
                <li>✓ Analyses intelligentes (ML)</li>
            </ul>
            <button type="button" class="btn-primary mt-6 w-full">Passer à Premium</button>
        </div>

        <p class="mt-4 text-sm text-slate-400">
            <a href="{{ route('landing') }}">← Retour à l'accueil</a>
        </p>
    </div>
</body>
</html>
