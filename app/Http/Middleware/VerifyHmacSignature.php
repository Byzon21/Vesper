<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Security\RequestIntegrity\HmacSigner;
use App\Support\Security\Vault\SecretVault;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vérification de l'intégrité des requêtes (HMAC).
 *
 * Les requêtes mutantes (POST/PUT/PATCH/DELETE) doivent porter les en-têtes :
 *   X-Vesper-Signature : signature HMAC-SHA256
 *   X-Vesper-Timestamp : horodatage UNIX (anti-rejeu)
 *
 * Seule la PWA officielle (qui possède la clé partagée, injectée côté serveur
 * lors du rendu initial et jamais exposée dans les DevTools/JS statique) peut
 * produire une signature valide. Bloque bots, scripts et pages de phishing.
 */
final class VerifyHmacSignature
{
    public function __construct(
        private readonly SecretVault $vault,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $signature = (string) $request->header('X-Vesper-Signature', '');
        $timestamp = (int) $request->header('X-Vesper-Timestamp', '0');

        $secret = $this->vault->resolve('vesper/hmac/signing-key')->reveal();
        $signer = new HmacSigner($secret);

        $valid = $signer->verify(
            $signature,
            $request->method(),
            $request->getPathInfo(),
            (string) $request->getContent(),
            $timestamp,
            (int) config('security.hmac.tolerance_seconds', 300),
        );

        if (! $valid) {
            abort(419, 'Signature de requête invalide.');
        }

        return $next($request);
    }
}
