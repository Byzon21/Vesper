-- Vesper — Migration 0005 : RLS (1/2) — helpers + profils, foyer, devises,
-- catégories, transactions et jambes.

-- Helpers d'accès aux transactions (security definer : pas de récursion RLS)
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

-- Activation RLS (Zero Trust : tout est refusé par défaut)
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
