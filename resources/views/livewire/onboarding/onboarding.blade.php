<div class="mx-auto max-w-lg">
    <div class="text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-blue to-brand-green text-white shadow-lg shadow-brand-blue/30">
            <x-icon name="sparkles" class="h-7 w-7" />
        </div>
        <h1 class="mt-4 text-2xl font-bold">Personnalisons votre expérience</h1>
        <p class="mt-1 text-sm text-slate-500">Quelques informations pour adapter votre tableau de bord à vos besoins.</p>
    </div>

    <form wire:submit="save" class="mt-8 space-y-6" wire:loading.class="opacity-60 pointer-events-none" wire:target="save">
        @error('form')
            <div class="rounded-xl border border-brand-pink/40 bg-brand-pink/10 px-4 py-3 text-sm text-brand-pink">
                {{ $message }}
            </div>
        @enderror

        {{-- 0. Photo de profil --}}
        <div class="flex items-center gap-4">
            <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-full bg-slate-100 ring-2 ring-brand-green/20">
                @if ($photo)
                    <img src="{{ $photo->temporaryUrl() }}" alt="Aperçu" class="h-full w-full object-cover">
                @else
                    <x-icon name="users" class="h-full w-full p-5 text-slate-300" />
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold">Photo de profil <span class="font-normal text-slate-400">(optionnel)</span></p>
                <input type="file" wire:model="photo" accept="image/*" class="mt-2 block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-green/10 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand-green hover:file:bg-brand-green/20">
                @error('photo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- 1. Identité --}}
        <div>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-brand-green">1 · Votre identité</h2>
            <div class="grid grid-cols-2 gap-3">
                <input wire:model="firstName" placeholder="Prénom" class="input">
                <input wire:model="lastName" placeholder="Nom" class="input">
            </div>
            @error('firstName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            @error('lastName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- 2. Pays --}}
        <div>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-brand-green">2 · Votre pays</h2>
            <select wire:model="country" class="input">
                <option value="">— Choisir un pays —</option>
                @foreach ($countryOptions as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
            @error('country') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- 3. Type de compte --}}
        <div>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-brand-green">3 · Type de compte</h2>
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" wire:model="accountType" value="personal" class="peer sr-only">
                    <div class="card !p-4 text-center transition peer-checked:border-brand-green peer-checked:ring-2 peer-checked:ring-brand-green/30">
                        <x-icon name="wallet" class="mx-auto h-6 w-6 text-brand-green" />
                        <p class="mt-2 font-semibold">Particulier</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" wire:model="accountType" value="couple" class="peer sr-only">
                    <div class="card !p-4 text-center transition peer-checked:border-brand-green peer-checked:ring-2 peer-checked:ring-brand-green/30">
                        <x-icon name="users" class="mx-auto h-6 w-6 text-brand-green" />
                        <p class="mt-2 font-semibold">Couple</p>
                    </div>
                </label>
            </div>
            @if ($accountType === 'couple')
                <input wire:model="partnerName" placeholder="Prénom de votre partenaire" class="input mt-3">
                @error('partnerName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            @endif
        </div>

        {{-- 4. Devises --}}
        <div>
            <h2 class="mb-1 text-sm font-semibold uppercase tracking-wide text-brand-green">4 · Vos devises</h2>
            <p class="mb-3 text-xs text-slate-500">Cochez une ou plusieurs devises — la première sera votre devise principale.</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($currencyOptions as $code => $label)
                    <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm transition has-[:checked]:border-brand-green has-[:checked]:bg-brand-green/5 dark:border-slate-700">
                        <input type="checkbox" wire:model="currencies" value="{{ $code }}" class="rounded border-slate-300 text-brand-green focus:ring-brand-green/30">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('currencies') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save" class="inline-flex items-center gap-2">
                Terminer
                <x-icon name="arrow-right" class="h-4 w-4" />
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
