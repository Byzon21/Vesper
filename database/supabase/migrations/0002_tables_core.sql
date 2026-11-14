-- Vesper — Migration 0002 : Tables cœur (profils, foyer, devises, catégories)

create table if not exists public.profiles (
    id            uuid primary key references auth.users (id) on delete cascade,
    display_name  text,
    avatar_path   text,
    language      text not null default 'fr',
    theme         text not null default 'system',
    base_currency char(3) not null default 'USD',
    created_at    timestamptz not null default now(),
    updated_at    timestamptz not null default now()
);

create table if not exists public.households (
    id            uuid primary key default gen_random_uuid(),
    name          text not null,
    created_by    uuid not null references public.profiles (id),
    base_currency char(3) not null default 'USD',
    created_at    timestamptz not null default now()
);

create table if not exists public.household_members (
    household_id uuid not null references public.households (id) on delete cascade,
    profile_id   uuid not null references public.profiles (id) on delete cascade,
    role         text not null default 'member' check (role in ('owner', 'member')),
    status       text not null default 'active' check (status in ('active', 'pending', 'rejected')),
    joined_at    timestamptz not null default now(),
    primary key (household_id, profile_id)
);

create table if not exists public.currencies (
    code      char(3) primary key,
    name      text not null,
    symbol    text not null,
    num_code  int,
    decimals  int not null default 2,
    region    text,
    is_active boolean not null default true
);

create table if not exists public.exchange_rates (
    id             bigint generated always as identity primary key,
    base_currency  char(3) not null references public.currencies (code),
    quote_currency char(3) not null references public.currencies (code),
    rate           numeric(18, 8) not null,
    source         text not null default 'manual',
    is_manual      boolean not null default false,
    household_id   uuid references public.households (id) on delete cascade,
    profile_id     uuid references public.profiles (id) on delete cascade,
    as_of          timestamptz not null default now()
);

create table if not exists public.categories (
    id           uuid primary key default gen_random_uuid(),
    household_id uuid references public.households (id) on delete cascade,
    profile_id   uuid references public.profiles (id) on delete cascade,
    name         text not null,
    icon         text not null default 'tag',
    color        text not null default '#6366f1',
    kind         text not null check (kind in ('expense', 'income')),
    parent_id    uuid references public.categories (id) on delete cascade,
    created_at   timestamptz not null default now(),
    check (household_id is not null or profile_id is not null)
);
