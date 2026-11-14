<div>
    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="mb-1 block text-sm font-medium">@lang('transactions.title')</label>
            <input type="text" wire:model="title" class="input" placeholder="Ex : Courses, Loyer…">
            @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-sm font-medium">@lang('transactions.usd_amount')</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">$</span>
                    <input type="text" inputmode="decimal" wire:model="usdAmount" class="input pl-7" placeholder="0.00">
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">@lang('transactions.cdf_amount')</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">FC</span>
                    <input type="text" inputmode="decimal" wire:model="cdfAmount" class="input pl-9" placeholder="0">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-sm font-medium">@lang('transactions.date')</label>
                <input type="date" wire:model="occurredAt" class="input">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">@lang('transactions.category')</label>
                <select wire:model="kind" class="input">
                    <option value="expense">@lang('dashboard.expenses')</option>
                    <option value="income">@lang('dashboard.income')</option>
                    <option value="transfer">Transfert</option>
                </select>
            </div>
        </div>

        @if ($unifiedTotal !== null)
            <div class="card flex items-center gap-2 text-sm">
                <x-icon name="chart" class="h-4 w-4 text-indigo-500" />
                <span>Total unifié : <strong>{{ $unifiedTotal }} {{ $baseCurrency }}</strong></span>
            </div>
        @endif

        <button type="submit" class="btn-primary w-full">
            <x-icon name="wallet" class="h-4 w-4" />
            @lang('transactions.save')
        </button>
    </form>
</div>
