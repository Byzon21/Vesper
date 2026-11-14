{{-- Navigation basse mobile-first (5 entrées, périmètre volontairement restreint) --}}
<nav class="safe-bottom fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
    <div class="mx-auto flex max-w-lg items-center justify-around py-2">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 text-xs {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-slate-500' }}">
            <x-icon name="home" class="h-5 w-5" />
            <span>@lang('nav.dashboard')</span>
        </a>
        <a href="{{ route('transactions') }}" class="flex flex-col items-center gap-0.5 text-xs {{ request()->routeIs('transactions') ? 'text-indigo-600' : 'text-slate-500' }}">
            <x-icon name="wallet" class="h-5 w-5" />
            <span>@lang('nav.transactions')</span>
        </a>
        <a href="{{ route('couple') }}" class="flex flex-col items-center gap-0.5 text-xs {{ request()->routeIs('couple') ? 'text-indigo-600' : 'text-slate-500' }}">
            <x-icon name="couple" class="h-5 w-5" />
            <span>@lang('nav.couple')</span>
        </a>
        <a href="{{ route('reports') }}" class="flex flex-col items-center gap-0.5 text-xs {{ request()->routeIs('reports') ? 'text-indigo-600' : 'text-slate-500' }}">
            <x-icon name="report" class="h-5 w-5" />
            <span>@lang('nav.reports')</span>
        </a>
        <a href="{{ route('settings') }}" class="flex flex-col items-center gap-0.5 text-xs {{ request()->routeIs('settings') ? 'text-indigo-600' : 'text-slate-500' }}">
            <x-icon name="settings" class="h-5 w-5" />
            <span>@lang('nav.settings')</span>
        </a>
    </div>
</nav>
