<?php

declare(strict_types=1);

use App\Http\Middleware\BotDetection;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\StrictRateLimiter;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Bootstrap Laravel 11 — pile de middlewares de sécurité
|--------------------------------------------------------------------------
| Ordre (défense en profondeur) :
|  1. SecurityHeaders     → HSTS, CSP stricte, X-Frame-Options, etc.
|  2. BotDetection        → blocage UA suspects, honeypot, chemins interdits
|  3. StrictRateLimiter   → anti-bruteforce / anti-scraping / anti-DoS
|  (VerifyHmacSignature est appliqué route par route sur les appels JSON.)
|--------------------------------------------------------------------------
*/

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
            BotDetection::class,
            StrictRateLimiter::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // En production : ne jamais exposer les détails d'erreur aux clients.
        $exceptions->dontReportDuplicates();
    })
    ->create();
