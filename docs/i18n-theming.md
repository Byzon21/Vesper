# i18n, Thème & Profils — Vesper

## 1. Internationalisation (4 langues)

- Fichiers JSON modulaires : `resources/lang/{en,fr,es,pt}.json`.
- Usage dans Blade : `@lang('clé')`, `__('clé')`.
- Locale persistée dans `profiles.language`, appliquée via `app()->setLocale()` (middleware).

| Clé | fr | en | es | pt |
|---|---|---|---|---|
| `nav.dashboard` | Tableau de bord | Dashboard | Panel de control | Painel |
| `transactions.add` | Nouvelle transaction | New transaction | Nueva transacción | Nova transação |
| `theme.dark` | Sombre | Dark | Oscuro | Escuro |

## 2. Thème Jour / Sombre

- Bascule fluide via Alpine.js : classe `dark` sur `<html>` (Tailwind `darkMode: 'class'`).
- **Anti-flash** : script inline (nonce CSP) applique le thème avant le paint.
- Préférence mémorisée en `localStorage` puis synchronisée avec `profiles.theme` (`light` / `dark` / `system`).
- `window.applyTheme()` / `window.toggleTheme()` exposés globalement.

## 3. Profils & photos

- **Mode personnel** : 1 photo de profil (`profiles.avatar_path`, Supabase Storage).
- **Mode couple** : 2 photos liées au foyer — chaque membre a son `avatar_path`, le foyer agrège les deux (politique RLS `profiles` autorise la lecture de l'avatar du partenaire).
- Import chiffré : les fichiers sont stockés dans un bucket Storage **privé** (RLS), servis via URL signées à durée limitée.

## 4. Périmètre des pages (UX épurée)

5 vues uniquement, via la navigation basse mobile :
**Tableau de bord · Transactions · Couple · Rapports · Paramètres** — aucune surcharge.
