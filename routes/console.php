<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tâches planifiées (scheduler)
|--------------------------------------------------------------------------
| Exécuter avec : php artisan schedule:work
*/

// Rafraîchissement périodique des taux de change (clé via Vault).
Schedule::command('vesper:refresh-fx-rates')->hourly();

// Ré-entraînement du classifieur ML (natif PHP ou microservice).
Schedule::command('vesper:train-ml')->dailyAt('03:00');
