<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Supabase\SupabaseClient;
use App\Services\Supabase\SupabaseUser;
use App\Support\Security\KeyHierarchy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Établissement de session Laravel à partir d'un JWT Supabase (Google OAuth).
 *
 * Étape finale du flux : le middleware AuthenticateSupabase a déjà vérifié le
 * JWT (JWKS). Ici on :
 *  1. provisionne (idempotent) la KEK dans le Vault et la DEK enveloppée en base,
 *  2. régénère la session (protection fixation de session),
 *  3. stocke l'identifiant + le jeton d'accès Supabase (chiffrés) côté serveur.
 *
 * Le jeton Supabase n'est JAMAIS renvoyé au client en clair au-delà de ce point :
 * il reste côté serveur pour les appels RLS ultérieurs.
 */
final class AuthController extends Controller
{
    public function __construct(
        private readonly KeyHierarchy $keys,
        private readonly SupabaseClient $supabase,
    ) {
    }

    public function establishSession(Request $request): JsonResponse
    {
        /** @var SupabaseUser|null $user */
        $user = $request->attributes->get('supabase_user');
        $token = (string) $request->attributes->get('supabase_access_token', '');

        if ($user === null || $token === '') {
            abort(401, 'Authentification incomplète.');
        }

        try {
            $this->provisionKeys($user->id);
        } catch (\Throwable $e) {
            // Le provisionnement des clés ne doit pas bloquer la connexion :
            // opération idempotente, réessayée lors d'un prochain appel.
            report($e);
        }

        // Récupère le profil (abonnement + préférences d'onboarding) depuis Supabase.
        $profile = $this->fetchProfile($user->id, $token);

        // Anti-fixation de session.
        $request->session()->regenerate();

        $request->session()->put('supabase_user_id', $user->id);
        $request->session()->put('supabase_email', $user->email);
        $request->session()->put('supabase_access_token', $token); // cookie chiffré (SESSION_ENCRYPT=true)
        $request->session()->put('trial_ends_at', $profile['trial_ends_at']);
        $request->session()->put('is_premium', $profile['is_premium']);
        $request->session()->put('is_onboarded', $profile['is_onboarded']);
        $request->session()->put('base_currency', $profile['base_currency']);
        $request->session()->put('currencies', $profile['currencies']);
        $request->session()->put('country', $profile['country']);
        $request->session()->put('account_type', $profile['account_type']);

        return response()->json([
            'status' => 'ok',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Confirme automatiquement l'email d'un utilisateur fraîchement inscrit.
     *
     * Inscription instantanée : quand Supabase exige une confirmation d'email,
     * le client reçoit l'UUID de l'utilisateur (sans session). On confirme ici
     * via l'API Admin (clé service_role) pour éviter l'envoi d'un email de
     * confirmation (service email par défaut de Supabase limité).
     */
    public function confirmEmail(Request $request): JsonResponse
    {
        // Inscription instantanée par défaut. Mettre AUTH_AUTO_CONFIRM=false
        // pour exiger la confirmation d'email (nécessite un SMTP configuré côté
        // Supabase, ex. Resend).
        if (! (bool) env('AUTH_AUTO_CONFIRM', true)) {
            return response()->json(['status' => 'disabled'], 403);
        }

        $userId = (string) $request->input('user_id', '');

        if ($userId === '' || ! preg_match('/^[0-9a-f-]{36}$/i', $userId)) {
            return response()->json(['status' => 'error'], 422);
        }

        $ok = $this->supabase->confirmUserEmail($userId);

        return response()->json(['status' => $ok ? 'ok' : 'error'], $ok ? 200 : 500);
    }

    /**
     * Déconnexion : invalide la session serveur (le jeton Supabase est
     * révoqué côté client avant cet appel).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['status' => 'ok']);
    }

    /**
     * Récupère le profil utilisateur (abonnement + préférences d'onboarding).
     *
     * @return array{trial_ends_at: ?string, is_premium: bool, is_onboarded: bool,
     *               base_currency: string, currencies: array<int, string>,
     *               country: ?string, account_type: string}
     */
    private function fetchProfile(string $userId, string $token): array
    {
        $defaults = [
            'trial_ends_at' => null,
            'is_premium' => false,
            'is_onboarded' => false,
            'base_currency' => 'USD',
            'currencies' => ['USD'],
            'country' => null,
            'account_type' => 'personal',
        ];

        try {
            $this->supabase->asUser($token);
            $rows = $this->supabase->select('profiles', [
                'select' => 'trial_ends_at,is_premium,is_onboarded,base_currency,currencies,country,account_type',
                'id' => 'eq.' . $userId,
            ]);

            $row = $rows[0] ?? [];

            $currencies = $row['currencies'] ?? null;
            if (! is_array($currencies) || $currencies === []) {
                $currencies = [$row['base_currency'] ?? 'USD'];
            }

            return [
                'trial_ends_at' => $row['trial_ends_at'] ?? null,
                'is_premium' => (bool) ($row['is_premium'] ?? false),
                'is_onboarded' => (bool) ($row['is_onboarded'] ?? false),
                'base_currency' => (string) ($row['base_currency'] ?? 'USD'),
                'currencies' => array_values($currencies),
                'country' => $row['country'] ?? null,
                'account_type' => (string) ($row['account_type'] ?? 'personal'),
            ];
        } catch (\Throwable) {
            // En cas d'échec de lecture, on laisse passer l'utilisateur authentifié.
            return $defaults;
        }
    }

    /**
     * Provisionnement des clés (VMK→KEK→DEK), idempotent.
     */
    private function provisionKeys(string $userId): void
    {
        // 1. KEK dans le Vault (créée si absente).
        $kek = $this->keys->userKek($userId);

        // 2. DEK enveloppée, stockée en base (encryption_keys).
        $this->supabase->asService();
        $existing = $this->supabase->select('encryption_keys', ['profile_id' => 'eq.' . $userId]);

        if ($existing === []) {
            $dek = $this->keys->generateDek();
            $wrapped = $this->keys->wrapDek($dek, $kek, 'user:' . $userId);

            $this->supabase->insert('encryption_keys', [
                'profile_id' => $userId,
                'wrapped_dek' => $wrapped,
                'kek_context' => 'user:' . $userId,
            ]);
        }
    }
}
