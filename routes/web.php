<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Middleware\AuthenticateSupabase;
use App\Http\Middleware\EnsureSupabaseSession;
use App\Livewire\Couple\CoupleSpace;
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

// Pages publiques d'authentification.
Route::get('/login', fn () => view('auth.login'))->name('login');
Route::get('/auth/callback', fn () => view('auth.callback'));

// Établissement de session depuis un JWT Supabase (Google OAuth).
Route::post('/auth/session', [AuthController::class, 'establishSession'])
    ->middleware(AuthenticateSupabase::class);
Route::post('/auth/logout', [AuthController::class, 'logout'])
    ->middleware(EnsureSupabaseSession::class);

// Pages protégées (session Supabase établie).
Route::middleware([EnsureSupabaseSession::class])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/transactions', TransactionList::class)->name('transactions');
    Route::get('/couple', CoupleSpace::class)->name('couple');
    Route::get('/reports', Reports::class)->name('reports');
    Route::get('/settings', Settings::class)->name('settings');
});


