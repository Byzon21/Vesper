<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion…</title>
    @vite(['resources/js/app.js'])
</head>
<body class="bg-slate-50 dark:bg-slate-950" data-callback="auth">
    <div class="flex min-h-screen items-center justify-center">
        <p class="text-sm text-slate-500">Connexion en cours…</p>
    </div>
</body>
</html>
