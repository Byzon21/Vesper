# Analytics UX & Machine Learning — Vesper

## 1. Analytics comportemental (UX)

Objectif : maîtrise totale de l'utilisation pour identifier les **frictions** et optimiser l'ergonomie mobile.

### Collecte côté client (`resources/js/analytics/tracker.js`)

- **Temps par écran** : `page_view` avec `duration_ms`, envoyé à `visibilitychange`/`pagehide`.
- **Dead clicks** : clic sur zone non interactive (`a, button, input, …` absents).
- **Rage clicks** : ≥ 3 clics sur le même élément en < 1 s (bouton non réactif).
- **Profondeur de scroll** : seuils 25/50/75/100 %.
- **Erreurs JS**, **inactivité** (idle), **focus**.
- Envoi en **lot** via `navigator.sendBeacon` (fiable sur mobile), file bornée (50).

### Ingestion serveur

- `POST /api/ux-events` → `AnalyticsController` → `UxAnalyticsService`.
- Sécurisé : session + **HMAC** + rate limiting global + validation stricte (taille bornée).
- **Vie privée** : aucune PII ; l'IP est **hachée** (`ip_hash`) ; événements rattachés au profil via **RLS**.

### Schéma

- `ux_sessions` : session (device, os, écran, durée).
- `ux_analytics_events` : `event_type` (`page_view`, `click`, `scroll`, `focus`, `error`, `idle`, `rage_click`, `dead_click`), page, composant, `element_id`, `metadata` JSONB.

## 2. Machine Learning (`App\ML`)

| Classe | Rôle |
|---|---|
| `SpendingClassifier` | Classifieur **k-NN** (Rubix ML), persisté, pour la **catégorisation automatique** des transactions |
| `SpendingForecaster` | **Prévisions de fin de mois** (moyenne mobile), **tendance linéaire** (moindres carrés), **alerte de dépassement de budget** |
| `InsightGenerator` | Orchestration : suggestions de catégorie + prévisions + conseils d'épargne |

Les insights sont **chiffrés** (`payload_ciphertext`) avant stockage dans `ml_insights`, et visibles par le profil ou le foyer (RLS).

### Flux ML

```
Historique déchiffré (transactions)
   → features (montant, jour, catégorie)
   → SpendingClassifier (entraîné périodiquement)
   → SpendingForecaster (projection / alertes)
   → InsightGenerator → chiffrement → ml_insights
```

Extensible vers un microservice Python : il suffit d'implémenter un provider/classifieur HTTP derrière le BFF (jamais exposé au client).

## 3. Rapports PDF

`PdfReportGenerator` (DOMPDF) génère à la volée des rapports personnalisés (global, par catégorie, par période, **couple vs personnel**), stocke le PDF dans Supabase Storage (RLS) et permet le téléchargement mobile. `isRemoteEnabled=false` (anti-SSRF).
