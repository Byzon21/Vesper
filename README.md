# Vesper

> **PWA ultra-sécurisée de gestion de dépenses financières personnelles et de couple** — mobile-first (Android & iOS).

Vesper est conçue selon une **architecture en couches strictes (Zero Trust)** où la couche de données est la dernière enceinte inviolable. La sécurité et l'inviolabilité des données sont le critère n°1 : chiffrement applicatif AES-256-GCM, hiérarchie de clés (Vault → KEK → DEK), Argon2id, signatures HMAC, CSP stricte, RLS Supabase.

---

## 🔐 Sécurité — points clés

| Exigence | Implémentation |
|---|---|
| Secrets jamais en clair | `SecretVault` (HashiCorp Vault en prod / fichier chiffré AES-256-GCM en dev), secrets éphémères auto-détruits (`SensitiveString`) |
| Chiffrement au repos | `FieldCipher` (AES-256-GCM) + `FieldEncryptor` (liaison AAD anti-substitution) |
| Hiérarchie de clés | `KeyHierarchy` : VMK → KEK (Vault) → DEK (base, enveloppée) |
| Hachage | `Hasher` : Argon2id (mots de passe), SHA-256 + sel (jetons) |
| Anti-phishing / intégrité | `HmacSigner` (signature HMAC + anti-rejeu) |
| Anti-bot / scraping | `BotDetection` + `StrictRateLimiter` (backoff exponentiel) |
| En-têtes HTTP | `SecurityHeaders` : HSTS, CSP stricte (nonce), X-Frame-Options, etc. |
| Isolation des données | RLS PostgreSQL (Supabase) + BFF (jamais de `service_role` côté client) |

## 🧱 Stack

- **Backend** : Laravel 11 (PHP 8.3) — BFF (Backend-for-Frontend)
- **Frontend** : Tailwind CSS + Alpine.js + Livewire 3 (stack TALL ultra-légère)
- **Icônes** : `papicons` (sprite SVG via composant `<x-icon>`)
- **Base de données** : Supabase (PostgreSQL + RLS + Storage + Auth)
- **Auth** : Google OAuth/OIDC via Supabase Auth → session Laravel chiffrée
- **ML** : Rubix ML (classifieur k-NN) + prévisions maison (prédictif)
- **PDF** : DOMPDF (rapports financiers)

## 📁 Structure

```
app/
├── Domain/
│   ├── Analytics/       # Événements UX (enum, VO, service)
│   └── Currency/        # Money, ExchangeRate, BiMonetaryService (RDC)
├── Http/
│   ├── Controllers/     # AuthController, AnalyticsController
│   └── Middleware/      # SecurityHeaders, BotDetection, HMAC, RateLimiter…
├── Livewire/            # Pages (Dashboard, Transactions, Couple, Reports, Settings)
├── ML/                  # SpendingClassifier, SpendingForecaster, InsightGenerator
├── Providers/           # SecurityServiceProvider (bindings)
├── Services/
│   ├── Reporting/       # PdfReportGenerator
│   └── Supabase/        # SupabaseClient, SupabaseJwtVerifier
└── Support/Security/
    ├── Crypto/          # FieldCipher, KeyDerivation, KeyEnvelope, Hasher…
    ├── Vault/           # SecretVault + pilotes (Local, HashiCorp)
    └── RequestIntegrity/# HmacSigner
database/supabase/migrations/  # 0001..0008 (schéma, RLS, devises)
resources/
├── js/                  # app.js, theme.js, analytics/tracker.js, pwa/
├── lang/                # en, fr, es, pt (JSON)
└── views/               # layouts, components (icon, bottom-nav), livewire
docs/                    # architecture, sécurité, BDD, multi-devises, analytics
```

## 🚀 Démarrage rapide

Prérequis : PHP 8.3+, Composer, Node 20+, compte Supabase.

```bash
composer install
npm install
cp .env.example .env        # puis renseigner les variables (secrets via Vault)
php artisan key:generate

# Appliquer les migrations sur Supabase (Supabase CLI)
supabase db push            # ou exécuter database/supabase/migrations/*.sql

npm run build               # assets PWA (Vite)
php artisan serve
```

> ⚠️ **Secrets** : ne mettez AUCUNE valeur réelle dans `.env`. En production, utilisez HashiCorp Vault (`VAULT_DRIVER=hashicorp`). Les chemins de secrets sont documentés dans `.env.example`.

## 📦 Déploiement GitHub

Le projet est prêt pour un transfert progressif vers GitHub. Le CLI `gh` n'étant pas installé, créez le dépôt puis poussez :

```bash
# 1. Créer le dépôt sur github.com (nom : Vesper, par ex. Byzon21/Vesper)
# 2. Lier et pousser
git remote add origin https://github.com/Byzon21/Vesper.git
git push -u origin main
```

---

Voir `docs/` pour l'architecture complète, la sécurité, le schéma BDD, le module multi-devises (RDC) et l'analytics UX.
