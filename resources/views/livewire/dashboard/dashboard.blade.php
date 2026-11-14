<div>
    <h1 class="mb-4 text-xl font-bold">@lang('dashboard.title')</h1>

    <div class="grid grid-cols-3 gap-3">
        <div class="card">
            <p class="text-xs text-slate-500">@lang('dashboard.balance')</p>
            <p class="mt-1 text-lg font-bold">—</p>
        </div>
        <div class="card">
            <p class="text-xs text-slate-500">@lang('dashboard.income')</p>
            <p class="mt-1 text-lg font-bold text-emerald-600">—</p>
        </div>
        <div class="card">
            <p class="text-xs text-slate-500">@lang('dashboard.expenses')</p>
            <p class="mt-1 text-lg font-bold text-red-600">—</p>
        </div>
    </div>

    <div class="mt-6">
        <h2 class="mb-2 text-sm font-semibold">@lang('transactions.add')</h2>
        @livewire('transactions.create-transaction')
    </div>
</div>
