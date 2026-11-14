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

        $this->provisionKeys($user->id);

        // Anti-fixation de session.
        $request->session()->regenerate();

        $request->session()->put('supabase_user_id', $user->id);
        $request->session()->put('supabase_email', $user->email);
        $request->session()->put('supabase_access_token', $token); // cookie chiffré (SESSION_ENCRYPT=true)

        return response()->json([
            'status' => 'ok',
            'user_id' => $user->id,
        ]);
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
