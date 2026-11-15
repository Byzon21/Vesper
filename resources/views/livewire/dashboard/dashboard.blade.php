<div wire:init="load">
    @if ($loaded)
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">@lang('dashboard.title')</h1>
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-blue to-brand-green text-white shadow-lg">
                <x-icon name="wallet" class="h-5 w-5" />
            </span>
        </div>

        {{-- Cartes statistiques --}}
        <div class="mt-4 grid grid-cols-3 gap-3">
            <div class="card">
                <p class="text-xs text-slate-500">@lang('dashboard.balance')</p>
                <p class="mt-1 text-lg font-bold">—</p>
            </div>
            <div class="card">
                <p class="text-xs text-slate-500">@lang('dashboard.income')</p>
                <p class="mt-1 text-lg font-bold text-brand-green">—</p>
            </div>
            <div class="card">
                <p class="text-xs text-slate-500">@lang('dashboard.expenses')</p>
                <p class="mt-1 text-lg font-bold text-brand-pink">—</p>
            </div>
        </div>

        {{-- Graphique : tendance mensuelle --}}
        <div class="card mt-4" wire:ignore>
            <h2 class="text-sm font-semibold">Évolution sur 6 mois</h2>
            <div class="mt-3 h-44">
                <canvas data-chart="line" data-label="Revenus"
                        data-labels='["Jul","Août","Sep","Oct","Nov","Déc"]'
                        data-values='[1200,1500,1350,1800,1650,2100]'></canvas>
            </div>
        </div>

        {{-- Graphique : dépenses par catégorie --}}
        <div class="card mt-4" wire:ignore>
            <h2 class="text-sm font-semibold">Dépenses par catégorie</h2>
            <div class="mt-3 h-44">
                <canvas data-chart="doughnut"
                        data-labels='["Logement","Alimentation","Transport","Loisirs"]'
                        data-values='[600,350,150,120]'></canvas>
            </div>
        </div>

        {{-- Ajout de transaction --}}
        <div class="mt-6">
            <h2 class="mb-2 text-sm font-semibold">@lang('transactions.add')</h2>
            @livewire('transactions.create-transaction')
        </div>
    @else
        {{-- Skeleton de chargement --}}
        <div class="animate-pulse">
            <div class="h-8 w-44 rounded-lg bg-slate-200 dark:bg-slate-700"></div>
            <div class="mt-4 grid grid-cols-3 gap-3">
                <div class="h-20 rounded-2xl bg-slate-200 dark:bg-slate-700"></div>
                <div class="h-20 rounded-2xl bg-slate-200 dark:bg-slate-700"></div>
                <div class="h-20 rounded-2xl bg-slate-200 dark:bg-slate-700"></div>
            </div>
            <div class="mt-4 h-44 rounded-2xl bg-slate-200 dark:bg-slate-700"></div>
            <div class="mt-4 h-44 rounded-2xl bg-slate-200 dark:bg-slate-700"></div>
            <div class="mt-4 h-24 rounded-2xl bg-slate-200 dark:bg-slate-700"></div>
        </div>
    @endif
</div>


