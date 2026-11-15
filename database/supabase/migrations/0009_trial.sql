-- ============================================================================
-- Vesper — Migration 0009 : Essai gratuit & abonnement Premium
-- ============================================================================

alter table public.profiles add column if not exists trial_ends_at timestamptz;
alter table public.profiles add column if not exists is_premium boolean not null default false;

-- À l'inscription, l'essai gratuit expire 7 jours plus tard.
create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
    insert into public.profiles (id, display_name, language, trial_ends_at)
    values (
        new.id,
        coalesce(new.raw_user_meta_data->>'full_name', split_part(new.email, '@', 1)),
        'fr',
        now() + interval '7 days'
    )
    on conflict (id) do nothing;
    return new;
end;
$$;
