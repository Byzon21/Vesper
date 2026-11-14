<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Supabase\SupabaseJwtVerifier;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentification Supabase (Google OAuth / OIDC) → session Laravel.
 *
 * Flux :
 *  1. La PWA s'authentifie auprès de Supabase Auth (Google) et obtient un JWT.
 *  2. Elle transmet ce JWT (en-tête Authorization: Bearer) sur /auth/session.
 *  3. Ce middleware vérifie le JWT (JWKS) puis délègue la création de la
 *     session chiffrée côté Laravel (voir AuthController::establishSession()).
 *
 * Les routes protégées n'acceptent plus que la session Laravel (cookie chiffré),
 * le JWT Supabase étant conservé chiffré côté serveur pour les appels RLS.
 */
final class AuthenticateSupabase
{
    public function __construct(
        private readonly SupabaseJwtVerifier $verifier,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->bearerToken($request);

        if ($token === null) {
            abort(Response::HTTP_UNAUTHORIZED, 'Jeton manquant.');
        }

        $user = $this->verifier->verify($token);

        // Expose l'utilisateur vérifié pour l'étape d'établissement de session.
        $request->attributes->set('supabase_user', $user);
        $request->attributes->set('supabase_access_token', $token);

        return $next($request);
    }

    private function bearerToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }
}
