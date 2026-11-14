# Analytics produit & Machine Learning — Vesper

## 1. Analytics comportemental — PostHog

L'analytics UX est entièrement délégué à **PostHog** (solution dédiée, plus adaptée qu'un tracker maison).

### Côté client (`resources/js/posthog.js`)

- SDK `posthog-js`, initialisé avec la clé projet publique (`phc_`) via `VITE_POSTHOG_KEY`.
- **Autocapture** : clics, **rage clicks** (détection native), **dead clicks**, champs, etc.
- **Temps par écran** : `capture_pageview` + `capture_pageleave`.
- **Session replay** : enregistrements pour identifier visuellement les zones de friction.
- **Confidentialité (app financière)** : `maskAllText`, `maskAllInputs`, `maskAllElementAttributes` ; identification par **UUID Supabase opaque** (jamais d'e-mail/PII) ; `posthog.reset()` à la déconnexion.

### Côté serveur (`app/Services/Analytics/PostHogService.php`)

- SDK `posthog/posthog-php` pour les **événements métier fiables** : `transaction created`, `report generated`, `budget exceeded`, etc.
- `distinctId` = UUID Supabase (aligné avec le frontend).

### Clés PostHog

| Clé | Préfixe | Usage | Exposition |
|---|---|---|---|
| Clé projet | `phc_` | JS (client) + capture serveur | Publique par conception |
| Clé personnelle | `phx_` | Admin API (requêtes de données) | **Secret strict** → Vault |

### Création du compte PostHog (à faire ensemble)

1. Ouvrir **https://us.posthog.com/signup** (ou `eu.posthog.com`) et créer un compte gratuit.
2. Créer un **projet** « Vesper ».
3. Récupérer la **clé projet** (`phc_…`) dans *Project settings → Project API key*.
4. (Optionnel) Générer une **clé personnelle** (`phx_…`) dans *Settings → Personal API keys*.
5. Renseigner dans `.env` :
   ```bash
   VITE_POSTHOG_KEY=phc_...
   POSTHOG_PROJECT_TOKEN=phc_...
   POSTHOG_HOST=https://us.i.posthog.com
   ```
6. En production, `POSTHOG_PERSONAL_KEY_VAULT` pointe vers le Vault (clé `phx_` jamais en clair).

## 2. Machine Learning (`App\ML`)

| Classe | Rôle |
|---|---|
| `SpendingClassifier` | Classifieur **k-NN** (Rubix ML), persisté, pour la **catégorisation automatique** des transactions |
| `SpendingForecaster` | **Prévisions de fin de mois** (moyenne mobile), **tendance linéaire** (moindres carrés), **alerte de dépassement de budget** |
| `InsightGenerator` | Orchestration : suggestions de catégorie + prévisions + conseils d'épargne |

Les insights sont **chiffrés** (`payload_ciphertext`) avant stockage dans `ml_insights`, et visibles par le profil ou le foyer (RLS).

Extensible vers un microservice Python : il suffit d'implémenter un provider/classifieur HTTP derrière le BFF (jamais exposé au client).

## 3. Rapports PDF

`PdfReportGenerator` (DOMPDF) génère à la volée des rapports personnalisés (global, par catégorie, par période, **couple vs personnel**), stocke le PDF dans Supabase Storage (RLS) et permet le téléchargement mobile. `isRemoteEnabled=false` (anti-SSRF).

