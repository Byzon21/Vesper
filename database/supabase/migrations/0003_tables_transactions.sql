-- Vesper — Migration 0003 : Transactions multi-devises, budgets, épargne

create table if not exists public.transactions (
    id              uuid primary key default gen_random_uuid(),
    household_id    uuid references public.households (id) on delete cascade,
    profile_id      uuid not null references public.profiles (id),
    category_id     uuid references public.categories (id),
    kind            text not null check (kind in ('expense', 'income', 'transfer')),
    scope           text not null default 'personal' check (scope in ('personal', 'shared')),
    title           text not null,
    note_ciphertext text,
    occurred_at     date not null,
    created_at      timestamptz not null default now(),
    updated_at      timestamptz not null default now()
);

-- Jambes monétaires (multi-devises, cas USD/CDF RDC)
create table if not exists public.transaction_legs (
    id                          uuid primary key default gen_random_uuid(),
    transaction_id              uuid not null references public.transactions (id) on delete cascade,
    currency_code               char(3) not null references public.currencies (code),
    amount_ciphertext           text not null,
    exchange_rate               numeric(18, 8),
    converted_amount_ciphertext text,
    rate_source                 text default 'manual',
    created_at                  timestamptz not null default now()
);

create table if not exists public.budgets (
    id                uuid primary key default gen_random_uuid(),
    household_id      uuid references public.households (id) on delete cascade,
    profile_id        uuid references public.profiles (id) on delete cascade,
    category_id       uuid references public.categories (id),
    amount_ciphertext text not null,
    currency_code     char(3) not null,
    period            text not null check (period in ('monthly', 'weekly', 'yearly', 'custom')),
    starts_on         date,
    ends_on           date,
    created_at        timestamptz not null default now(),
    check (household_id is not null or profile_id is not null)
);

create table if not exists public.savings_goals (
    id                       uuid primary key default gen_random_uuid(),
    household_id             uuid references public.households (id) on delete cascade,
    profile_id               uuid references public.profiles (id) on delete cascade,
    name                     text not null,
    target_amount_ciphertext text not null,
    currency_code            char(3) not null,
    deadline                 date,
    created_at               timestamptz not null default now(),
    check (household_id is not null or profile_id is not null)
);
