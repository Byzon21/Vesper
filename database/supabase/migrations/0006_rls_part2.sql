-- Vesper — Migration 0006 : RLS (2/2) — budgets, épargne, clés, analytics,
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
