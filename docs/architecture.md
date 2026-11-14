# Architecture Globale — Vesper

## 1. Paradigme : architecture en couches strictes (Zero Trust)

L'application est structurée en **4 couches** où chaque couche ne peut dépendre que des couches inférieures (règle de dépendance unidirectionnelle, principe SOLID **DIP**). La **couche de données est la dernière enceinte inviolable** : même si les couches supérieures sont compromises, les données restent chiffrées et isolées.

```
┌─────────────────────────────────────────────────────────────┐
│ 1. PRÉSENTATION (UI)                                        │
│    Blade + Livewire + Alpine + Tailwind + papicons          │
│    → aucun secret, aucune logique métier sensible           │
├─────────────────────────────────────────────────────────────┤
│ 2. APPLICATION (Use Cases / Controllers / Middleware)       │
│    AuthController, AnalyticsController, Livewire Components │
│    → orchestration, validation, sessions                    │
├─────────────────────────────────────────────────────────────┤
│ 3. DOMAINE (logique métier pure — OOP / SOLID)              │
│    Currency (Money, BiMonetaryService), Analytics (UxEvent) │
│    → Value Objects immuables, interfaces (repositories)     │
├─────────────────────────────────────────────────────────────┤
│ 4. INFRASTRUCTURE (données & sécurité)                      │
│    SupabaseClient, SecretVault, FieldCipher, RLS PostgreSQL │
│    → dernière enceinte : chiffrement + RLS + Vault          │
└─────────────────────────────────────────────────────────────┘
```

## 2. Flux de données & modèle BFF (Backend-for-Frontend)

**Aucun secret ni appel tiers sensible n'est effectué depuis le client.** Tout transite par Laravel.

```
PWA (navigateur)
   │  1. Google OAuth via Supabase Auth → JWT
   ▼
Laravel (BFF)  ── valide le JWT (JWKS RS256)
   │                ── crée une session chiffrée (cookie)
   │  2. Appels métier signés HMAC + session
   ▼
Laravel → Supabase (PostgREST) avec le JWT utilisateur
   │         ── RLS PostgreSQL applique l'isolation par ligne
   ▼
PostgreSQL (données chiffrées au champ : AES-256-GCM)
```

- Le **JWT Supabase** et la clé `service_role` ne quittent **jamais** le serveur.
- La **clé HMAC** de signature est injectée par session (jamais dans le JS statique).

## 3. Hiérarchie des clés (Zero Trust Absolu)

```
Vault Master Key (VMK) — hors application (HashiCorp Vault / KMS)
        │ chiffre au repos les secrets du Vault
        ▼
Key Encryption Key (KEK) — 1 par utilisateur, DANS LE VAULT uniquement
        │ enveloppe (AES-256-GCM)
        ▼
Data Encryption Key (DEK) — 1 par utilisateur + 1 par foyer, EN BASE
        │ chiffre les champs
        ▼
Montants, notes, soldes (colonnes *_ciphertext)
```

**Conséquences d'inviolabilité :**
- Fuite de la base seule → inexploitable (DEK enveloppées par KEK absente).
- Fuite du Vault seul → inexploitable (KEK sans les DEK/ciphertexts).
- Isolation couple : la DEK du foyer est enveloppée **pour chaque membre** (`household_keys`), un membre ne peut lire que sa propre enveloppe.

## 4. Décisions d'architecture (ADR résumées)

| Décision | Choix | Justification |
|---|---|---|
| Framework PHP | **Laravel 11** | Couplage natif Livewire/Inertia + écosystème sécurité + BFF |
| Frontend | **TALL** (Tailwind + Alpine + Livewire 3) | Ultra-léger, SSR partiel, peu de JS = surface d'attaque réduite |
| Icônes | **papicons** | Bibliothèque SVG légère, rendue via sprite `<use>` |
| Auth | **Supabase Auth (Google OIDC) → session Laravel** | JWT vérifié (JWKS), session chiffrée côté serveur |
| Données | **Supabase (PostgreSQL + RLS)** | Isolation par ligne native + storage sécurisé |
| Secrets | **Vault** (HashiCorp / local chiffré) | Secrets éphémères, révocables, jamais en clair |
| Chiffrement | **AES-256-GCM** (field-level) | Chiffrement authentifié avant insertion en base |

## 5. Périmètre des pages (UX épurée)

Volontairement **5 vues** à forte valeur ajoutée, accessibles via la navigation basse :

1. **Tableau de bord** — solde, revenus, dépenses, alertes.
2. **Transactions** — saisie/gestion (dont bimétaire USD/CDF).
3. **Couple** — partage du foyer, 2 photos de profil, transactions partagées.
4. **Rapports** — analyses, insights ML, export PDF.
5. **Paramètres** — langue, thème, devise, profils.

## 6. Points d'extension (ML & microservices)

Le domaine est découplé par interfaces (`ExchangeRateProvider`, `CurrencyRepository`, `SpendingClassifier`). Le module ML peut évoluer :
- **natif PHP** (Rubix ML, inclus),
- ou **microservice** : un `ExchangeRateProvider`/`Classifier` peut appeler un service Python via HTTP (toujours derrière le BFF, jamais exposé).
