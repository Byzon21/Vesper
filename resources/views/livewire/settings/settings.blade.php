<div>
    <h1 class="mb-4 text-xl font-bold">@lang('settings.title')</h1>
    <div class="card space-y-4 text-sm">
        <div class="flex items-center justify-between">
            <span>@lang('settings.theme')</span>
            <button type="button" data-action="toggleTheme" class="btn-ghost">
                <x-icon name="settings" class="h-4 w-4" />
            </button>
        </div>
        <div class="flex items-center justify-between">
            <span>@lang('settings.language')</span>
            <select class="input w-auto">
                <option value="fr">Français</option>
                <option value="en">English</option>
                <option value="es">Español</option>
                <option value="pt">Português</option>
            </select>
        </div>
        <div class="flex items-center justify-between">
            <span>@lang('settings.currency')</span>
            <select class="input w-auto">
                <option value="USD">USD — $</option>
                <option value="CDF">CDF — FC</option>
            </select>
        </div>

        <div class="border-t border-slate-200 pt-4 dark:border-slate-800">
            <button type="button" data-action="signOut" class="btn-ghost w-full text-red-600">
                Déconnexion
            </button>
        </div>
    </div>
</div>
