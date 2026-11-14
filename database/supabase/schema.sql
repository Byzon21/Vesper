-- ===== 0001_extensions_and_helpers.sql =====
-- ============================================================================
-- Vesper â€” Migration Supabase 0001 : Extensions & Fonctions d'aide
-- PostgreSQL 15+ (Supabase). Toutes les tables vivent dans le schÃ©ma `public`.
-- ============================================================================

-- UUID v4 natifs + gÃ©nÃ©ration d'octets alÃ©atoires (nonces/sels cÃ´tÃ© SQL).
create extension if not exists pgcrypto;

-- ----------------------------------------------------------------------------
-- DÃ©clencheur gÃ©nÃ©rique de mise Ã  jour de `updated_at`
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
-- Approvisionnement automatique du profil Ã  l'inscription (auth.users)
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
-- Appartenance Ã  un foyer (utilisÃ©e par les politiques RLS)
-- security definer : Ã©vite la rÃ©cursion RLS lors des vÃ©rifications d'adhÃ©sion.
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
-- RÃ´le du membre courant dans un foyer ('owner' | 'member' | null)
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


-- ===== 0002_tables_core.sql =====
-- Vesper â€” Migration 0002 : Tables cÅ“ur (profils, foyer, devises, catÃ©gories)

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


-- ===== 0003_tables_transactions.sql =====
-- Vesper â€” Migration 0003 : Transactions multi-devises, budgets, Ã©pargne

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

-- Jambes monÃ©taires (multi-devises, cas USD/CDF RDC)
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


-- ===== 0004_keys_analytics.sql =====
-- Vesper â€” Migration 0004 : ClÃ©s de chiffrement, insights ML, rapports, index

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


-- ===== 0005_rls_part1.sql =====
-- Vesper â€” Migration 0005 : RLS (1/2) â€” helpers + profils, foyer, devises,
-- catÃ©gories, transactions et jambes.

-- Helpers d'accÃ¨s aux transactions (security definer : pas de rÃ©cursion RLS)
create or replace function public.can_access_transaction(txid uuid)
returns boolean language sql security definer set search_path = public stable
as $$
    select exists (
        select 1 from public.transactions t
        where t.id = txid
          and (t.profile_id = auth.uid()
               or (t.scope = 'shared' and public.is_household_member(t.household_id)))
    );
$$;

create or replace function public.is_transaction_creator(txid uuid)
returns boolean language sql security definer set search_path = public stable
as $$
    select exists (
        select 1 from public.transactions t
        where t.id = txid and t.profile_id = auth.uid()
    );
$$;

-- Activation RLS (Zero Trust : tout est refusÃ© par dÃ©faut)
alter table public.profiles            enable row level security;
alter table public.households          enable row level security;
alter table public.household_members   enable row level security;
alter table public.currencies          enable row level security;
alter table public.exchange_rates      enable row level security;
alter table public.categories          enable row level security;
alter table public.transactions        enable row level security;
alter table public.transaction_legs    enable row level security;

-- ------------------------------------------------------------------ profiles
create policy p_profiles_select on public.profiles for select using (
    id = auth.uid() or exists (
        select 1 from public.household_members a
        join public.household_members b on b.household_id = a.household_id
        where a.profile_id = profiles.id and a.status = 'active'
          and b.profile_id = auth.uid() and b.status = 'active'
    )
);
create policy p_profiles_insert on public.profiles for insert with check (id = auth.uid());
create policy p_profiles_update on public.profiles for update using (id = auth.uid());

-- ---------------------------------------------------------------- households
create policy p_households_select on public.households for select using (public.is_household_member(id));
create policy p_households_insert on public.households for insert with check (created_by = auth.uid());
create policy p_households_update on public.households for update using (public.household_role(id) = 'owner');
create policy p_households_delete on public.households for delete using (public.household_role(id) = 'owner');

-- ---------------------------------------------------------- household_members
create policy p_members_select on public.household_members for select using (public.is_household_member(household_id));
create policy p_members_insert on public.household_members for insert with check (public.household_role(household_id) = 'owner');
create policy p_members_update on public.household_members for update using (public.household_role(household_id) = 'owner');
create policy p_members_delete on public.household_members for delete using (public.household_role(household_id) = 'owner');

-- --------------------------------------------------------------- currencies
create policy p_currencies_select on public.currencies for select using (auth.role() = 'authenticated');

-- ----------------------------------------------------------- exchange_rates
create policy p_fx_select on public.exchange_rates for select using (
    (household_id is null and profile_id is null)
    or profile_id = auth.uid()
    or public.is_household_member(household_id)
);
create policy p_fx_insert on public.exchange_rates for insert with check (profile_id = auth.uid());
create policy p_fx_update on public.exchange_rates for update using (profile_id = auth.uid());
create policy p_fx_delete on public.exchange_rates for delete using (profile_id = auth.uid());

-- -------------------------------------------------------------- categories
create policy p_cat_select on public.categories for select using (
    profile_id = auth.uid() or public.is_household_member(household_id)
);
create policy p_cat_insert on public.categories for insert with check (
    profile_id = auth.uid() or public.household_role(household_id) in ('owner', 'member')
);
create policy p_cat_update on public.categories for update using (
    profile_id = auth.uid() or public.household_role(household_id) in ('owner', 'member')
);
create policy p_cat_delete on public.categories for delete using (
    profile_id = auth.uid() or public.household_role(household_id) = 'owner'
);

-- ------------------------------------------------------------ transactions
create policy p_tx_select on public.transactions for select using (
    profile_id = auth.uid()
    or (scope = 'shared' and public.is_household_member(household_id))
);
create policy p_tx_insert on public.transactions for insert with check (profile_id = auth.uid());
create policy p_tx_update on public.transactions for update using (
    profile_id = auth.uid()
    or (scope = 'shared' and public.is_household_member(household_id))
);
create policy p_tx_delete on public.transactions for delete using (profile_id = auth.uid());

-- ---------------------------------------------------------- transaction_legs
create policy p_leg_select on public.transaction_legs for select using (public.can_access_transaction(transaction_id));
create policy p_leg_insert on public.transaction_legs for insert with check (public.is_transaction_creator(transaction_id));
create policy p_leg_update on public.transaction_legs for update using (public.is_transaction_creator(transaction_id));
create policy p_leg_delete on public.transaction_legs for delete using (public.is_transaction_creator(transaction_id));


-- ===== 0006_rls_part2.sql =====
-- Vesper â€” Migration 0006 : RLS (2/2) â€” budgets, Ã©pargne, clÃ©s, analytics,
-- insights ML et rapports.

alter table public.budgets             enable row level security;
alter table public.savings_goals       enable row level security;
alter table public.encryption_keys     enable row level security;
alter table public.household_keys      enable row level security;
alter table public.ml_insights         enable row level security;
alter table public.reports             enable row level security;

-- ------------------------------------------------------------------- budgets
create policy p_budget_select on public.budgets for select using (
    profile_id = auth.uid() or public.is_household_member(household_id)
);
create policy p_budget_insert on public.budgets for insert with check (
    profile_id = auth.uid() or public.is_household_member(household_id)
);
create policy p_budget_update on public.budgets for update using (
    profile_id = auth.uid() or public.household_role(household_id) in ('owner', 'member')
);
create policy p_budget_delete on public.budgets for delete using (
    profile_id = auth.uid() or public.household_role(household_id) = 'owner'
);

-- ------------------------------------------------------------- savings_goals
create policy p_saving_select on public.savings_goals for select using (
    profile_id = auth.uid() or public.is_household_member(household_id)
);
create policy p_saving_insert on public.savings_goals for insert with check (
    profile_id = auth.uid() or public.is_household_member(household_id)
);
create policy p_saving_update on public.savings_goals for update using (
    profile_id = auth.uid() or public.household_role(household_id) in ('owner', 'member')
);
create policy p_saving_delete on public.savings_goals for delete using (
    profile_id = auth.uid() or public.household_role(household_id) = 'owner'
);

-- ----------------------------------------------------------- encryption_keys
create policy p_dek_select on public.encryption_keys for select using (profile_id = auth.uid());
create policy p_dek_insert on public.encryption_keys for insert with check (profile_id = auth.uid());
create policy p_dek_update on public.encryption_keys for update using (profile_id = auth.uid());

-- ------------------------------------------------------------ household_keys
create policy p_hdek_select on public.household_keys for select using (profile_id = auth.uid());
create policy p_hdek_insert on public.household_keys for insert with check (profile_id = auth.uid());

-- ------------------------------------------------------------- ml_insights
create policy p_ml_select on public.ml_insights for select using (
    profile_id = auth.uid() or public.is_household_member(household_id)
);
create policy p_ml_insert on public.ml_insights for insert with check (
    profile_id = auth.uid() or public.is_household_member(household_id)
);

-- ------------------------------------------------------------------ reports
create policy p_report_select on public.reports for select using (
    profile_id = auth.uid() or public.is_household_member(household_id)
);
create policy p_report_insert on public.reports for insert with check (profile_id = auth.uid());


-- ===== 0007_currencies_africa.sql =====
-- Vesper â€” Migration 0007 : RÃ©fÃ©rentiel devises â€” AFRIQUE (ISO 4217)
-- Inclut les devises des petits pays (Loti du Lesotho, Dollar namibienâ€¦)
-- et le cas bimÃ©taire critique USD/CDF (RDC).

insert into public.currencies (code, name, symbol, num_code, decimals, region) values
-- Afrique centrale
('XAF', 'Franc CFA (BEAC)',   'FCFA', 950, 0, 'Afrique centrale'),
('CDF', 'Franc congolais',    'FC',   976, 2, 'Afrique centrale'),
('AOA', 'Kwanza angolais',    'Kz',   973, 2, 'Afrique centrale'),
('STN', 'Dobra santomÃ©en',    'Db',   930, 2, 'Afrique centrale'),

-- Afrique de l''Est
('BIF', 'Franc burundais',        'FBu', 108, 0, 'Afrique de l''Est'),
('DJF', 'Franc djiboutien',       'Fdj', 262, 0, 'Afrique de l''Est'),
('ERN', 'Nakfa Ã©rythrÃ©en',        'Nfk', 232, 2, 'Afrique de l''Est'),
('ETB', 'Birr Ã©thiopien',         'Br',  230, 2, 'Afrique de l''Est'),
('KES', 'Shilling kÃ©nyan',        'KSh', 404, 2, 'Afrique de l''Est'),
('RWF', 'Franc rwandais',         'RF',  646, 0, 'Afrique de l''Est'),
('SOS', 'Shilling somalien',      'Sh',  706, 2, 'Afrique de l''Est'),
('SSP', 'Livre sud-soudanaise',   'SSP', 728, 2, 'Afrique de l''Est'),
('TZS', 'Shilling tanzanien',     'TSh', 834, 2, 'Afrique de l''Est'),
('UGX', 'Shilling ougandais',     'USh', 800, 0, 'Afrique de l''Est'),

-- Afrique australe
('ZAR', 'Rand sud-africain',    'R',   710, 2, 'Afrique australe'),
('BWP', 'Pula botswanais',      'P',   072, 2, 'Afrique australe'),
('LSL', 'Loti du Lesotho',      'L',   426, 2, 'Afrique australe'),
('NAD', 'Dollar namibien',      'N$',  516, 2, 'Afrique australe'),
('SZL', 'Lilangeni d''Eswatini', 'E',  748, 2, 'Afrique australe'),
('ZMW', 'Kwacha zambien',       'ZK',  967, 2, 'Afrique australe'),
('MWK', 'Kwacha malawite',      'MK',  454, 2, 'Afrique australe'),
('MZN', 'Metical mozambicain',  'MT',  943, 2, 'Afrique australe'),
('MGA', 'Ariary malgache',      'Ar',  969, 2, 'Afrique australe'),
('MUR', 'Roupie mauricienne',   'Rs',  480, 2, 'Afrique australe'),
('SCR', 'Roupie seychelloise',  'SR',  690, 2, 'Afrique australe'),
('KMF', 'Franc comorien',       'CF',  174, 0, 'Afrique australe'),
('ZWG', 'Zimbabwe Gold (ZiG)',  'ZiG', 924, 2, 'Afrique australe'),

-- Afrique de l''Ouest
('XOF', 'Franc CFA (BCEAO)',  'CFA',  952, 0, 'Afrique de l''Ouest'),
('NGN', 'Naira nigÃ©rian',     'â‚¦',    566, 2, 'Afrique de l''Ouest'),
('GHS', 'Cedi ghanÃ©en',       'GHâ‚µ',  936, 2, 'Afrique de l''Ouest'),
('GMD', 'Dalasi gambien',     'D',    270, 2, 'Afrique de l''Ouest'),
('SLL', 'Leone sierra-lÃ©onais','Le',  694, 2, 'Afrique de l''Ouest'),
('LRD', 'Dollar libÃ©rien',    'L$',   430, 2, 'Afrique de l''Ouest'),
('CVE', 'Escudo cap-verdien', 'Esc',  132, 2, 'Afrique de l''Ouest'),
('GNF', 'Franc guinÃ©en',      'FG',   324, 0, 'Afrique de l''Ouest'),
('MRU', 'Ouguiya mauritanien','UM',   929, 2, 'Afrique de l''Ouest'),

-- Afrique du Nord
('EGP', 'Livre Ã©gyptienne', 'EÂ£', 818, 2, 'Afrique du Nord'),
('DZD', 'Dinar algÃ©rien',   'DA', 012, 2, 'Afrique du Nord'),
('MAD', 'Dirham marocain',  'DH', 504, 2, 'Afrique du Nord'),
('TND', 'Dinar tunisien',   'DT', 788, 3, 'Afrique du Nord'),
('LYD', 'Dinar libyen',     'LD', 434, 3, 'Afrique du Nord'),
('SDG', 'Livre soudanaise', 'SDG', 938, 2, 'Afrique du Nord')
on conflict (code) do nothing;


-- ===== 0008_currencies_world.sql =====
-- Vesper â€” Migration 0008 : RÃ©fÃ©rentiel devises â€” MONDE (ISO 4217)

insert into public.currencies (code, name, symbol, num_code, decimals, region) values
-- AmÃ©rique du Nord & centrale
('USD', 'Dollar amÃ©ricain',      '$',   840, 2, 'AmÃ©rique du Nord'),
('CAD', 'Dollar canadien',       'C$',  124, 2, 'AmÃ©rique du Nord'),
('MXN', 'Peso mexicain',         'MX$', 484, 2, 'AmÃ©rique du Nord'),
('GTQ', 'Quetzal guatÃ©maltÃ¨que', 'Q',   320, 2, 'AmÃ©rique centrale'),
('CRC', 'ColÃ³n costaricien',     'â‚¡',   188, 2, 'AmÃ©rique centrale'),
('DOP', 'Peso dominicain',       'RD$', 214, 2, 'AmÃ©rique centrale'),
('HTG', 'Gourde haÃ¯tienne',      'G',   332, 2, 'AmÃ©rique centrale'),
('JMD', 'Dollar jamaÃ¯cain',      'J$',  388, 2, 'AmÃ©rique centrale'),
('TTD', 'Dollar de TrinitÃ©',     'TT$', 780, 2, 'AmÃ©rique centrale'),

-- AmÃ©rique du Sud
('BRL', 'Real brÃ©silien',   'R$',  986, 2, 'AmÃ©rique du Sud'),
('ARS', 'Peso argentin',    'AR$', 032, 2, 'AmÃ©rique du Sud'),
('CLP', 'Peso chilien',     'CL$', 152, 0, 'AmÃ©rique du Sud'),
('COP', 'Peso colombien',   'CO$', 170, 2, 'AmÃ©rique du Sud'),
('PEN', 'Sol pÃ©ruvien',     'S/',  604, 2, 'AmÃ©rique du Sud'),
('UYU', 'Peso uruguayen',   '$U',  858, 2, 'AmÃ©rique du Sud'),
('PYG', 'GuaranÃ­ paraguayen','â‚²',  600, 0, 'AmÃ©rique du Sud'),
('BOB', 'Boliviano bolivien','Bs', 068, 2, 'AmÃ©rique du Sud'),
('VES', 'BolÃ­var vÃ©nÃ©zuÃ©lien','Bs', 928, 2, 'AmÃ©rique du Sud'),
('GYD', 'Dollar guyanien',   'G$',  328, 2, 'AmÃ©rique du Sud'),
('SRD', 'Dollar surinamais', 'Sr$', 968, 2, 'AmÃ©rique du Sud'),

-- Europe
('EUR', 'Euro',             'â‚¬',  978, 2, 'Europe'),
('GBP', 'Livre sterling',   'Â£',  826, 2, 'Europe'),
('CHF', 'Franc suisse',     'CHF', 756, 2, 'Europe'),
('NOK', 'Couronne norvÃ©gienne', 'kr', 578, 2, 'Europe'),
('SEK', 'Couronne suÃ©doise',    'kr', 752, 2, 'Europe'),
('DKK', 'Couronne danoise',     'kr', 208, 2, 'Europe'),
('PLN', 'Zloty polonais',       'zÅ‚', 985, 2, 'Europe'),
('CZK', 'Couronne tchÃ¨que',     'KÄ', 203, 2, 'Europe'),
('HUF', 'Forint hongrois',      'Ft', 348, 2, 'Europe'),
('RON', 'Leu roumain',          'lei', 946, 2, 'Europe'),
('BGN', 'Lev bulgare',          'Ð»Ð²', 975, 2, 'Europe'),
('ISK', 'Couronne islandaise',  'kr', 352, 0, 'Europe'),
('UAH', 'Hryvnia ukrainienne',  'â‚´', 980, 2, 'Europe'),
('MDL', 'Leu moldave',          'L',  498, 2, 'Europe'),
('RSD', 'Dinar serbe',          'din', 941, 2, 'Europe'),
('MKD', 'Denar macÃ©donien',     'den', 807, 2, 'Europe'),
('ALL', 'Lek albanais',         'L',  008, 2, 'Europe'),
('BAM', 'Mark convertible',     'KM', 977, 2, 'Europe'),
('BYN', 'Rouble biÃ©lorusse',    'Br', 933, 2, 'Europe'),
('RUB', 'Rouble russe',         'â‚½',  643, 2, 'Europe'),
('TRY', 'Livre turque',         'â‚º',  949, 2, 'Europe'),

-- Asie & OcÃ©anie
('JPY', 'Yen japonais',      'Â¥',   392, 0, 'Asie'),
('CNY', 'Yuan chinois',      'Â¥',   156, 2, 'Asie'),
('HKD', 'Dollar de Hong Kong','HK$', 344, 2, 'Asie'),
('TWD', 'Dollar taÃ¯wanais',  'NT$', 901, 2, 'Asie'),
('KRW', 'Won sud-corÃ©en',    'â‚©',   410, 0, 'Asie'),
('SGD', 'Dollar singapourien','S$', 702, 2, 'Asie'),
('MYR', 'Ringgit malaisien', 'RM',  458, 2, 'Asie'),
('THB', 'Baht thaÃ¯landais',  'à¸¿',   764, 2, 'Asie'),
('IDR', 'Roupie indonÃ©sienne','Rp', 360, 2, 'Asie'),
('PHP', 'Peso philippin',    'â‚±',   608, 2, 'Asie'),
('VND', 'Dong vietnamien',   'â‚«',   704, 0, 'Asie'),
('INR', 'Roupie indienne',   'â‚¹',   356, 2, 'Asie'),
('PKR', 'Roupie pakistanaise','Rs', 586, 2, 'Asie'),
('BDT', 'Taka bangladais',   'à§³',   050, 2, 'Asie'),
('LKR', 'Roupie srilankaise', 'Rs', 144, 2, 'Asie'),
('NPR', 'Roupie nÃ©palaise',  'Rs',  524, 2, 'Asie'),
('MMK', 'Kyat birman',       'K',   104, 2, 'Asie'),
('KHR', 'Riel cambodgien',   'áŸ›',   116, 2, 'Asie'),
('LAK', 'Kip laotien',       'â‚­',   418, 2, 'Asie'),
('MNT', 'Tugrik mongol',     'â‚®',   496, 2, 'Asie'),
('AUD', 'Dollar australien', 'A$',  036, 2, 'OcÃ©anie'),
('NZD', 'Dollar nÃ©o-zÃ©landais','NZ$', 554, 2, 'OcÃ©anie'),
('FJD', 'Dollar fidjien',    'FJ$', 242, 2, 'OcÃ©anie'),
('PGK', 'Kina papou',        'K',   598, 2, 'OcÃ©anie'),
('SBD', 'Dollar des Salomon','SI$', 090, 2, 'OcÃ©anie'),
('VUV', 'Vatu vanuatuan',    'Vt',  548, 0, 'OcÃ©anie'),

-- Moyen-Orient
('AED', 'Dirham des Ã‰mirats', 'Ø¯.Ø¥', 784, 2, 'Moyen-Orient'),
('SAR', 'Riyal saoudien',     'ï·¼',   682, 2, 'Moyen-Orient'),
('QAR', 'Riyal qatari',       'ï·¼',   634, 2, 'Moyen-Orient'),
('KWD', 'Dinar koweÃ¯tien',    'Ø¯.Ùƒ', 414, 3, 'Moyen-Orient'),
('BHD', 'Dinar bahreÃ¯ni',     'Ø¯.Ø¨', 048, 3, 'Moyen-Orient'),
('OMR', 'Rial omanais',       'ï·¼',   512, 3, 'Moyen-Orient'),
('ILS', 'Shekel israÃ©lien',   'â‚ª',   376, 2, 'Moyen-Orient'),
('JOD', 'Dinar jordanien',    'Ø¯.Ø§', 400, 3, 'Moyen-Orient'),
('LBP', 'Livre libanaise',    'Ù„.Ù„', 422, 2, 'Moyen-Orient'),
('IQD', 'Dinar irakien',      'Ø¯.Ø¹', 368, 3, 'Moyen-Orient'),
('IRR', 'Rial iranien',       'ï·¼',   364, 2, 'Moyen-Orient'),
('SYP', 'Livre syrienne',     'Ù„.Ø³', 760, 2, 'Moyen-Orient'),
('YER', 'Rial yÃ©mÃ©nite',      'ï·¼',   886, 2, 'Moyen-Orient')
on conflict (code) do nothing;
