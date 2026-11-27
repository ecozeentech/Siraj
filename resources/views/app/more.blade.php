<x-layouts.app title="More" headerless flush>
    <section class="relative overflow-hidden text-white" style="padding-top: var(--safe-top); background: linear-gradient(180deg, #0b6b38 0%, #084e2c 100%)">
        <div class="pattern-geo pointer-events-none absolute inset-0 opacity-70"></div>
        <div class="relative mx-auto flex max-w-md items-center gap-3 px-5 pb-8 pt-4">
            <x-ui.avatar :user="$user" size="xl" class="size-20 ring-2 ring-white" />
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-2xl font-bold leading-tight">{{ $user->name }}</h1>
                <p class="truncate text-sm text-white/90">{{ $user->email }}</p>
                @if ($user->isPremium())
                    <span class="mt-2 inline-flex items-center rounded-full bg-white/15 px-2.5 py-1 text-xs font-semibold text-gold-200">Siraj Plus</span>
                @endif
            </div>
        </div>
    </section>
    <div class="relative z-10 mx-auto -mt-4 max-w-md space-y-4 px-4 pb-6">
        <nav class="overflow-hidden rounded-[1.4rem] bg-surface shadow-card" aria-label="Account">
            @foreach ([
                ['layout-dashboard', __('My Dashboard'), 'app.profile.dashboard'],
                ['wallet', __('Subscription & Wallet'), 'app.wallet.index'],
                ['users', __('Family Management'), 'app.family.index'],
                ['settings', __('Settings'), 'app.settings.index'],
                ['life-buoy', __('Help & Support'), 'app.support.index'],
                ['info', __('About SIRAJ'), 'app.explore.about'],
            ] as [$icon, $label, $route])
                <a href="{{ route($route) }}" class="icon-pop flex min-h-14 items-center gap-3 border-b border-line px-4 last:border-0">
                    <span class="grid size-9 place-items-center rounded-full bg-mint text-[#128a49]"><x-dynamic-component :component="'lucide-'.$icon" class="size-4" /></span>
                    <span class="flex-1 font-semibold">{{ $label }}</span>
                    <x-lucide-chevron-right class="size-4 text-muted" />
                </a>
            @endforeach
        </nav>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="icon-pop flex min-h-12 w-full items-center justify-center rounded-full bg-[#e7c56a] text-base font-bold text-[#3d3414]">{{ __('Log Out') }}</button>
        </form>

        <section>
            <h2 class="mb-2 px-1 text-xs font-semibold uppercase tracking-widest text-muted">{{ __('Account') }}</h2>
            <div class="overflow-hidden rounded-2xl bg-surface shadow-card">
                <a href="{{ route('app.referrals') }}" class="icon-pop flex min-h-12 items-center gap-3 border-b border-line px-4"><x-lucide-gift class="size-4 text-[#128a49]" /><span class="flex-1 font-medium">{{ __('Refer & Earn') }}</span><x-lucide-chevron-right class="size-4 text-muted" /></a>
                <a href="{{ route('app.notifications') }}" class="icon-pop flex min-h-12 items-center gap-3 px-4"><x-lucide-bell class="size-4 text-[#128a49]" /><span class="flex-1 font-medium">{{ __('Notifications') }}</span><x-lucide-chevron-right class="size-4 text-muted" /></a>
            </div>
        </section>
        <section>
            <h2 class="mb-2 px-1 text-xs font-semibold uppercase tracking-widest text-muted">{{ __('Explore') }}</h2>
            <div class="overflow-hidden rounded-2xl bg-surface shadow-card">
                @foreach ([['app.explore.features', __('Features')], ['app.explore.pricing', __('Pricing')], ['app.explore.about', __('About')], ['app.explore.contact', __('Contact')]] as [$route, $label])
                    <a href="{{ route($route) }}" class="flex min-h-12 items-center justify-between border-b border-line px-4 font-medium last:border-0">{{ $label }}<x-lucide-chevron-right class="size-4 text-muted" /></a>
                @endforeach
            </div>
        </section>
        <section>
            <h2 class="mb-2 px-1 text-xs font-semibold uppercase tracking-widest text-muted">{{ __('Legal') }}</h2>
            <div class="overflow-hidden rounded-2xl bg-surface shadow-card">
                <a href="{{ route('app.explore.privacy') }}" class="flex min-h-12 items-center justify-between border-b border-line px-4 font-medium">{{ __('Privacy') }}<x-lucide-chevron-right class="size-4 text-muted" /></a>
                <a href="{{ route('app.explore.terms') }}" class="flex min-h-12 items-center justify-between px-4 font-medium">{{ __('Terms') }}<x-lucide-chevron-right class="size-4 text-muted" /></a>
            </div>
        </section>
    </div>
</x-layouts.app>
