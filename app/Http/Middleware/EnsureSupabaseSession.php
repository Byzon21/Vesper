<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérifie qu'une session Supabase (établie par AuthController) est active.
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

        return $next($request);
    }
}
