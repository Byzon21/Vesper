-- ============================================================================
-- Vesper — Migration 0010 : Onboarding (identité, pays, type de compte, devises)
-- ============================================================================

alter table public.profiles add column if not exists first_name text;
alter table public.profiles add column if not exists last_name text;
alter table public.profiles add column if not exists country text;
alter table public.profiles add column if not exists account_type text not null default 'personal' check (account_type in ('personal','couple'));
alter table public.profiles add column if not exists partner_name text;
alter table public.profiles add column if not exists currencies text[] not null default '{}';
alter table public.profiles add column if not exists is_onboarded boolean not null default false;
