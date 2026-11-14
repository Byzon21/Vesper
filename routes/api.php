<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes API (JSON, sans état)
|--------------------------------------------------------------------------
| Aucune clé secrète n'est exposée ici. Les appels tiers sensibles passent
| par des proxys côté serveur. Ces routes sont protégées par les middlewares
| globaux : SecurityHeaders, BotDetection, StrictRateLimiter.
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
