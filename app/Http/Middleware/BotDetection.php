<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Détection heuristique des bots & scanners de vulnérabilités.
 *
 * Signaux bloquants :
 *  - User-Agent absent ou dégradé (curl/wget/python-requests…)
 *  - Honeypot rempli (champ caché "website" : les bots le remplissent, les
 *    humains ne le voient pas)
 *  - Accès direct à des chemins sensibles ou à des outils (.env, .git, wp-*…)
 *
 * En cas de suspicion : réponse 403 et (en production) défi comportemental.
 */
final class BotDetection
{
    private const SUSPICIOUS_UA = [
        'curl/', 'wget/', 'python-requests', 'libwww', 'scrapy',
        'sqlmap', 'nikto', 'nmap', 'masscan', 'gobuster', 'dirbuster',
        'headlesschrome', 'phantomjs', 'zgrab', 'censys', 'shodan',
    ];

    private const FORBIDDEN_PATHS = [
        '.env', '.git', '.aws', 'wp-admin', 'wp-login', 'xmlrpc.php',
        'config.php', 'phpmyadmin', 'actuator', 'server-status',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isSuspiciousUserAgent($request)) {
            abort(403, 'Accès refusé.');
        }

        if ($this->hitsForbiddenPath($request)) {
            abort(404); // masquer l'existence de la ressource
        }

        if ($request->isMethod('post') && $request->filled('website')) {
            // Honeypot : champ invisible rempli par un bot.
            abort(403, 'Accès refusé.');
        }

        return $next($request);
    }

    private function isSuspiciousUserAgent(Request $request): bool
    {
        $ua = strtolower((string) $request->header('User-Agent', ''));

        if ($ua === '') {
            return true;
        }

        foreach (self::SUSPICIOUS_UA as $pattern) {
            if (str_contains($ua, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function hitsForbiddenPath(Request $request): bool
    {
        $path = strtolower($request->getPathInfo());

        foreach (self::FORBIDDEN_PATHS as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
