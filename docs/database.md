# Base de données Supabase — Vesper

Schéma PostgreSQL géré par Supabase, avec **Row Level Security** partout (refus par défaut).

## Tables (migrations `database/supabase/migrations/`)

| Migration | Contenu |
|---|---|
| `0001` | Extensions (`pgcrypto`), trigger `handle_new_user`, helpers `is_household_member`, `household_role` |
| `0002` | `profiles`, `households`, `household_members`, `currencies`, `exchange_rates`, `categories` |
| `0003` | `transactions`, `transaction_legs`, `budgets`, `savings_goals` |
| `0004` | `encryption_keys`, `household_keys`, `ml_insights`, `reports` + index |
| `0005` | RLS (1/2) : profils, foyer, devises, catégories, transactions, jambes |
| `0006` | RLS (2/2) : budgets, épargne, clés, insights, rapports |
| `0007` | Devises — Afrique (CDF, XAF, XOF, LSL, NAD, ZWG…) |
| `0008` | Devises — Monde (USD, EUR, JPY, …) |

## Modèle relationnel

```
auth.users ─1:1─ profiles ──┬── household_members ──┬── households
                            │                       │
                            ├── transactions (créateur)
                            └── encryption_keys (DEK user)

households ─1:N─ household_keys (DEK foyer enveloppée par membre)
transactions ─1:N─ transaction_legs (multi-devises)
transactions ─N:1─ categories
```

## Conventions de chiffrement

- Colonnes sensibles suffixées `_ciphertext` : `amount_ciphertext`, `note_ciphertext`, `converted_amount_ciphertext`, `payload_ciphertext`, `target_amount_ciphertext`, `wrapped_dek`.
- Chaque utilisateur a une **DEK** (`encryption_keys.wrapped_dek`, enveloppée par sa KEK du Vault).
- Chaque foyer a une **DEK** enveloppée pour chaque membre (`household_keys.wrapped_dek`).

## Politiques RLS — principes

- `profiles` : lecture par soi-même ou partenaire du même foyer (avatars visibles en mode couple).
- `transactions` : `profile_id = auth.uid()` **ou** (`scope='shared'` et membre du foyer) → isole les transactions personnelles.
- `transaction_legs` : accès via `can_access_transaction()` (security definer), modification réservée au créateur (`is_transaction_creator()`).
- `exchange_rates` : taux globaux lisibles ; taux manuels visibles/modifiables par leur propriétaire.
- `encryption_keys` / `household_keys` : uniquement sa propre enveloppe (`profile_id = auth.uid()`).
- `ml_insights`, `reports` : son propre profil ou foyer.

Les fonctions `security definer` (`is_household_member`, `can_access_transaction`, …) évitent toute récursion RLS tout en restant liées à `auth.uid()`.
