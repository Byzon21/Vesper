-- Vesper — Migration 0004 : Clés de chiffrement, insights ML, rapports, index

create table if not exists public.encryption_keys (
    profile_id  uuid primary key references public.profiles (id) on delete cascade,
    wrapped_dek text not null,
    kek_context text not null default 'user',
    algorithm   text not null default 'aes-256-gcm',
    created_at  timestamptz not null default now(),
    updated_at  timestamptz not null default now()
);

create table if not exists public.household_keys (
    household_id uuid not null references public.households (id) on delete cascade,
    profile_id   uuid not null references public.profiles (id) on delete cascade,
    wrapped_dek  text not null,
    primary key (household_id, profile_id),
    created_at   timestamptz not null default now()
);

create table if not exists public.ml_insights (
    id                 uuid primary key default gen_random_uuid(),
    household_id       uuid references public.households (id) on delete cascade,
    profile_id         uuid references public.profiles (id) on delete cascade,
    model_version      text not null,
    insight_type       text not null,
    payload_ciphertext text not null,
    period             text,
    created_at         timestamptz not null default now()
);

create table if not exists public.reports (
    id           uuid primary key default gen_random_uuid(),
    household_id uuid references public.households (id) on delete cascade,
    profile_id   uuid not null references public.profiles (id),
    kind         text not null,
    period       text,
    storage_path text not null,
    generated_at timestamptz not null default now()
);

create index if not exists idx_transactions_household_date
    on public.transactions (household_id, occurred_at desc);
create index if not exists idx_transactions_profile_date
    on public.transactions (profile_id, occurred_at desc);
create index if not exists idx_legs_transaction
    on public.transaction_legs (transaction_id);
create index if not exists idx_fx_pair
    on public.exchange_rates (base_currency, quote_currency, as_of desc);
