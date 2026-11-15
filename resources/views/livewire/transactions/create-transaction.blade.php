<div>
    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="mb-1 block text-sm font-medium">@lang('transactions.title')</label>
            <input type="text" wire:model="title" class="input" placeholder="Ex : Courses, Loyer…">
            @error('title') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="mb-1 block text-sm font-medium">Montant</label>
                <input type="text" inputmode="decimal" wire:model="amount" class="input" placeholder="0.00">
                @error('amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Devise</label>
                <select wire:model="currency" class="input">
                    @foreach ($availableCurrencies as $code)
                        <option value="{{ $code }}">{{ $currencyLabels[$code] ?? $code }}</option>
                    @endforeach
                </select>
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

        <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                <x-icon name="wallet" class="h-4 w-4" />
                @lang('transactions.save')
            </span>
            <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                </svg>
                Enregistrement…
            </span>
        </button>
    </form>
</div>

