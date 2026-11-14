-- ============================================================================
-- Vesper — Migration Supabase 0001 : Extensions & Fonctions d'aide
-- PostgreSQL 15+ (Supabase). Toutes les tables vivent dans le schéma `public`.
-- ============================================================================

-- Autoriser la création de fonctions SQL qui référencent des tables créées
-- plus tard dans le script (ex: is_household_member → household_members).
-- Sans ceci, PostgreSQL valide le corps des fonctions `language sql` dès la
-- création et échoue sur les tables pas encore créées.
set check_function_bodies = off;

-- UUID v4 natifs + génération d'octets aléatoires (nonces/sels côté SQL).
create extension if not exists pgcrypto;

-- ----------------------------------------------------------------------------
-- Déclencheur générique de mise à jour de `updated_at`
-- ----------------------------------------------------------------------------
create or replace function public.set_updated_at()
returns trigger
language plpgsql
as $$
begin
    new.updated_at = now();
    return new;
end;
$$;

-- ----------------------------------------------------------------------------
-- Approvisionnement automatique du profil à l'inscription (auth.users)
-- ----------------------------------------------------------------------------
create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
    insert into public.profiles (id, display_name, language)
    values (
        new.id,
        coalesce(new.raw_user_meta_data->>'full_name', split_part(new.email, '@', 1)),
        'fr'
    )
    on conflict (id) do nothing;
    return new;
end;
$$;

drop trigger if exists on_auth_user_created on auth.users;
create trigger on_auth_user_created
    after insert on auth.users
    for each row execute procedure public.handle_new_user();

-- ----------------------------------------------------------------------------
-- Appartenance à un foyer (utilisée par les politiques RLS)
-- security definer : évite la récursion RLS lors des vérifications d'adhésion.
-- ----------------------------------------------------------------------------
create or replace function public.is_household_member(hid uuid)
returns boolean
language sql
security definer
set search_path = public
stable
as $$
    select exists (
        select 1
        from public.household_members hm
        where hm.household_id = hid
          and hm.profile_id = auth.uid()
          and hm.status = 'active'
    );
$$;

-- ----------------------------------------------------------------------------
-- Rôle du membre courant dans un foyer ('owner' | 'member' | null)
-- ----------------------------------------------------------------------------
create or replace function public.household_role(hid uuid)
returns text
language sql
security definer
set search_path = public
stable
as $$
    select hm.role
    from public.household_members hm
    where hm.household_id = hid
      and hm.profile_id = auth.uid()
      and hm.status = 'active'
    limit 1;
$$;
