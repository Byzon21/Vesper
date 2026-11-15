<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Supabase\SupabaseClient;
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
    public function __construct(
        private readonly SupabaseClient $supabase,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('supabase_user_id')) {
            return redirect()->route('login');
        }

        // Onboarding : quand la session indique « non onboardé », on re-vérifie
        // en base (Supabase). Le session()->put() effectué à la soumission
        // Livewire peut ne pas avoir été persisté au moment de la redirection,
        // ce qui causait une boucle onboarding → dashboard → onboarding.
        if (! $request->session()->get('is_onboarded', false) && ! $request->routeIs('onboarding')) {
            if (! $this->isOnboarded($request)) {
                return redirect()->route('onboarding');
            }
            $request->session()->put('is_onboarded', true);
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

    /**
     * Vérifie en base si l'utilisateur a complété son onboarding.
     */
    private function isOnboarded(Request $request): bool
    {
        try {
            $userId = (string) $request->session()->get('supabase_user_id');
            $token = (string) $request->session()->get('supabase_access_token');

            if ($userId === '' || $token === '') {
                return false;
            }

            $this->supabase->asUser($token);
            $rows = $this->supabase->select('profiles', [
                'select' => 'is_onboarded',
                'id' => 'eq.' . $userId,
            ]);

            return (bool) ($rows[0]['is_onboarded'] ?? false);
        } catch (\Throwable) {
            return false;
        }
    }
}
