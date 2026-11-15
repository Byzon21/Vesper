<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie qu'une session Supabase (établie par AuthController) est active ET
 * que l'abonnement est valide (essai gratuit non expiré ou compte premium).
 *
 * Remplace le middleware `auth` classique : il n'y a PAS de table `users`
 * locale — Supabase est la source de vérité. L'identifiant utilisateur est
 * conservé en session (`supabase_user_id`).
 */
final class EnsureSupabaseSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('supabase_user_id')) {
            return redirect()->route('login');
        }

        // Abonnement : essai expiré et non premium → redirection vers la
        // page de tarification.
        if (! $request->session()->get('is_premium', false)) {
            $trialEndsAt = $request->session()->get('trial_ends_at');

            if ($trialEndsAt !== null && now()->greaterThan(Carbon::parse($trialEndsAt))) {
                return redirect()->route('pricing');
            }
        }

        return $next($request);
    }
}
