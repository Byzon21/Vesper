<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limiting strict (anti-bruteforce, anti-scraping, anti-DoS).
 *
 * Deux clés de limitation :
 *  - par IP          : protège l'infrastructure (scanners, bots)
 *  - par utilisateur : protège les comptes (bruteforce, énumération)
 *
 * Backoff exponentiel : plus une source est pénalisée, plus la fenêtre de
 * blocage s'allonge (1 min → 2 min → 4 min → … plafonné à 1 h).
 */
final class StrictRateLimiter
{
    public function handle(Request $request, Closure $next): Response
    {
        $ipKey = 'ip:' . $request->ip();
        $userKey = 'user:' . ($request->user()?->getAuthIdentifier() ?? 'guest');

        $ipLimit = (int) config('security.rate_limit.per_ip', 120);
        $userLimit = (int) config('security.rate_limit.per_user', 300);

        if (RateLimiter::tooManyAttempts($ipKey, $ipLimit)) {
            return $this->blocked($request, $ipKey);
        }

        if (RateLimiter::tooManyAttempts($userKey, $userLimit)) {
            return $this->blocked($request, $userKey);
        }

        RateLimiter::hit($ipKey, 60);
        RateLimiter::hit($userKey, 60);

        return $next($request);
    }

    private function blocked(Request $request, string $key): Response
    {
        // Backoff exponentiel (plafonné à 1 h) pour pénaliser les récidivistes.
        $attempts = RateLimiter::attempts($key);
        $decay = min(60 * (2 ** max(0, $attempts - 1)), 3600);
        RateLimiter::hit($key, $decay);

        return response()->json([
            'message' => 'Trop de requêtes. Réessayez plus tard.',
        ], Response::HTTP_TOO_MANY_REQUESTS);
    }
}

