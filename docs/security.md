# Sécurité — Vesper (Zero Trust Absolu)

## 1. Vault centralisé (Secrets Management)

Aucun secret en clair dans le code, le `.env` de production ou la base.

- **Interface** : `App\Support\Security\Vault\SecretVault` (`resolve/store/revoke`).
- **Pilote production** : `HashiCorpVault` — authentification **AppRole**, jeton à TTL court (~5 min), lecture KV v2, **révocation du jeton dans `finally`**. Le secret est renvoyé sous forme de `SensitiveString` (auto-destruction en mémoire).
- **Pilote dev/CI** : `LocalEncryptedVault` — un fichier JSON **chiffré AES-256-GCM** avec une clé maîtresse issue de l'environnement (jamais versionné).
- `VaultManager` sélectionne le pilote selon `config('vault.driver')`.

> Chemins de secrets conventionnels : `vesper/supabase/service_role_key`, `vesper/fx/openexchangerates`, `vesper/hmac/signing-key`, `vesper/keys/users/{id}/kek`.

## 2. Chiffrement des données (Field-Level Encryption)

- `FieldCipher` : **AES-256-GCM** via libsodium. Chiffrement **authentifié** (le tag GCM garantit l'intégrité).
- `FieldEncryptor` : chiffre une valeur en la **liant à son contexte** (propriétaire + table + colonne) via l'**AAD**, empêchant la copie d'un ciphertext d'une ligne à une autre (attaque par substitution).
- `EncryptedField` : format versionné `ves1:<nonce>:<ciphertext>` stockable en `TEXT`.
- Les colonnes sensibles (`amount_ciphertext`, `note_ciphertext`, `converted_amount_ciphertext`, `payload_ciphertext`, …) ne contiennent **jamais** de clair.

## 3. Hachage (OWASP)

- `Hasher::password()` : **Argon2id** (64 MiB, t=4) via `password_hash`, vérification en temps constant.
- `Hasher::token()` : clés API/jetons hachés **irréversiblement** (SHA-256 + sel unique), format `sel$hash`, vérification `hash_equals`.
- `KeyDerivation` : **Argon2id** (`sodium_crypto_pwhash`) pour dériver des clés de 32 octets depuis des secrets de faible entropie.

## 4. Hiérarchie de clés

`KeyHierarchy` orchestre : VMK (hors app) → **KEK** (Vault, 1/utilisateur) → **DEK** (base, enveloppée AES-256-GCM par la KEK). `KeyEnvelope` fournit `wrap/unwrap`. La DEK du foyer est enveloppée pour chaque membre (`household_keys`).

## 5. Intégrité des requêtes (anti-phishing / anti-bot)

- `HmacSigner` : signature **HMAC-SHA256** sur `METHODE\npath\nsha256(body)\ntimestamp`, avec **fenêtre de tolérance** (anti-rejeu).
- `VerifyHmacSignature` : appliqué aux appels JSON mutables (`/api/*`). La clé est injectée **par session** (jamais dans le JS statique), ce qui rend toute page de phishing ou script automatisé incapable de signer.
- Livewire possède sa propre protection (CSRF + fingerprint de composant) : il n'est pas soumis au HMAC.

## 6. En-têtes & anti-bot

- `SecurityHeaders` : **HSTS** (max-age 1 an, includeSubDomains), **CSP stricte** (`script-src 'self' 'nonce-…'`, `object-src 'none'`, `frame-ancestors 'none'`), `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, COOP/CORP.
- `BotDetection` : bloque UA suspects (curl, sqlmap, headless…), honeypot (`website`), chemins interdits (`.env`, `.git`, …).
- `StrictRateLimiter` : deux clés (IP + utilisateur) avec **backoff exponentiel** (1 min → 1 h).

## 7. Transport

TLS 1.3 partout (`SESSION_SECURE_COOKIE=true`), cookies de session chiffrés (`SESSION_ENCRYPT=true`).

## 8. Auth Supabase → session Laravel

1. PWA s'authentifie (Google OAuth) → JWT Supabase.
2. `AuthenticateSupabase` vérifie le JWT via **JWKS** (`SupabaseJwtVerifier`).
3. `AuthController::establishSession` régénère la session (anti-fixation), provisionne les clés, stocke le JWT chiffré côté serveur.
