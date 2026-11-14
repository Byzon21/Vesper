<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
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
 * Un backoff exponentiel est appliqué : plus une source est pénalisée, plus la
 * fenêtre de blocage s'allonge.
 */
final class StrictRateLimiter
{
    public function handle(Request $request, Closure $next): Response
    {
        $ipKey = 'ip:' . $request->ip();
        $userKey = 'user:' . ($request->user()?->getAuthIdentifier() ?? 'guest');

        $ipLimit = Limit::perMinute((int) config('security.rate_limit.per_ip', 120))->by($ipKey);
        $userLimit = Limit::perMinute((int) config('security.rate_limit.per_user', 300))->by($userKey);

        $rateLimiter = RateLimiter::limiter('vesper-global', fn (Request $r) => [$ipLimit, $userLimit]);

        if ($rateLimiter($request)->tooManyAttempts()) {
            $this->applyBackoff($request, $ipKey, $userKey);

            return response()->json([
                'message' => 'Trop de requêtes. Réessayez plus tard.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $rateLimiter($request)->hit();

        return $next($request);
    }

    private function applyBackoff(Request $request, string ...$keys): void
    {
        foreach ($keys as $key) {
            $attempts = RateLimiter::attempts($key);

            // Backoff exponentiel : 1 min → 2 min → 4 min → … (plafonné à 1 h).
            $decay = min(60 * pow(2, max(0, $attempts - 1)), 3600);
            RateLimiter::hit($key, $decay);
        }
    }
}
