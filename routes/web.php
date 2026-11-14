<?php

declare(strict_types=1);

use App\Http\Controllers\AnalyticsController;
use App\Http\Middleware\VerifyHmacSignature;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Reports\Reports;
use App\Livewire\Settings\Settings;
use App\Livewire\Transactions\TransactionList;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Web — PWA Vesper
| Périmètre volontairement restreint (pas de surcharge de pages) :
| Tableau de bord, Transactions, Couple, Rapports, Paramètres.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/transactions', TransactionList::class)->name('transactions');
    Route::get('/couple', \App\Livewire\Couple\CoupleSpace::class)->name('couple');
    Route::get('/reports', Reports::class)->name('reports');
    Route::get('/settings', Settings::class)->name('settings');

    // Ingestion analytics UX (signée HMAC + session + rate limiting global).
    Route::post('/api/ux-events', AnalyticsController::class)
        ->middleware(VerifyHmacSignature::class);
});

// Établissement de session depuis un JWT Supabase (Google OAuth).
Route::post('/auth/session', [\App\Http\Controllers\AuthController::class, 'establishSession'])
    ->middleware(\App\Http\Middleware\AuthenticateSupabase::class);
