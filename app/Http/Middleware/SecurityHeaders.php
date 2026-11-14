<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-têtes de sécurité HTTP stricts (défense en profondeur).
 *
 * Applique : HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy,
 * Permissions-Policy et une Content-Security-Policy stricte avec nonce.
 *
 * Le nonce CSP est partagé avec les vues via View::share() et exposé via
 * l'attribut de requête 'csp_nonce' afin que Blade/Livewire puissent l'injecter
 * dans les balises <script> (voir x-layout et @livewireScripts(['nonce' => ...])).
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $nonce = $this->generateNonce();

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('X-XSS-Protection', '0'); // obsolète, CSP fait le travail

        if (config('security.hsts.enabled', true)) {
            $maxAge = (int) config('security.hsts.max_age', 31536000);
            $hsts = 'max-age=' . $maxAge;
            if (config('security.hsts.include_subdomains', true)) {
                $hsts .= '; includeSubDomains';
            }
            $response->headers->set('Strict-Transport-Security', $hsts);
        }

        $response->headers->set('Content-Security-Policy', $this->buildCsp($nonce));

        // Partage du nonce avec la couche Vue.
        View::share('cspNonce', $nonce);
        $request->attributes->set('csp_nonce', $nonce);

        return $response;
    }

    private function buildCsp(string $nonce): string
    {
        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "script-src 'self' 'nonce-{$nonce}'",
            // 'unsafe-inline' styles : nécessaire pour Tailwind/Livewire ; n'exécute pas de code.
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob: https://*.supabase.co",
            "font-src 'self' data:",
            "connect-src 'self' https://*.supabase.co",
            "manifest-src 'self'",
            "worker-src 'self' blob:",
            "frame-src 'none'",
        ];

        return implode('; ', $directives);
    }

    private function generateNonce(): string
    {
        return base64_encode(random_bytes(16));
    }
}
