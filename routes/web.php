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
| Pages publiques (marketing + authentification)
|--------------------------------------------------------------------------
| Tout visiteur lambda peut découvrir l'application (landing), la tarification
| (pricing), puis s'inscrire (register) ou se connecter (login).
*/

Route::get('/', fn () => view('landing'))->name('landing');
Route::get('/pricing', fn () => view('pricing'))->name('pricing');
Route::get('/login', fn () => view('auth.login'))->name('login');
Route::get('/register', fn () => view('auth.register'))->name('register');
Route::get('/auth/callback', fn () => view('auth.callback'));

// Établissement / destruction de session (Supabase JWT).
Route::post('/auth/session', [AuthController::class, 'establishSession'])
    ->middleware(AuthenticateSupabase::class);
Route::post('/auth/confirm', [AuthController::class, 'confirmEmail']);
Route::post('/auth/logout', [AuthController::class, 'logout'])
    ->middleware(EnsureSupabaseSession::class);

/*
|--------------------------------------------------------------------------
| Pages protégées (session Supabase + abonnement actif)
|--------------------------------------------------------------------------
| Périmètre volontairement restreint : Tableau de bord, Transactions, Couple,
| Rapports, Paramètres.
*/

Route::middleware([EnsureSupabaseSession::class])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/transactions', TransactionList::class)->name('transactions');
    Route::get('/couple', CoupleSpace::class)->name('couple');
    Route::get('/reports', Reports::class)->name('reports');
    Route::get('/settings', Settings::class)->name('settings');
});



