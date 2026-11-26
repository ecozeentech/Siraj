<x-layouts.app title="Profile">
    <x-slot:header>
        <header class="hero-green pattern-geo px-4 pb-8 lg:rounded-b-[2rem]" style="padding-top: calc(1rem + var(--safe-top))">
            <div class="mx-auto flex max-w-6xl flex-col items-center text-center">
                <x-ui.avatar :user="$user" size="xl" class="ring-4" />
                <h1 class="mt-3 text-xl font-bold">{{ $user->name }}</h1>
                <p class="text-sm text-white/75">{{ $user->email }}</p>
                <span class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-gold-300 px-3 py-1 text-xs font-bold text-forest-950"><x-lucide-crown class="size-3.5" />{{ $plan?->name ?? 'SIRAJ Free' }}</span>
            </div>
        </header>
    </x-slot:header>
    <div class="mx-auto max-w-xl space-y-4 py-5">
        <div class="card divide-y divide-line overflow-hidden">
            <x-ui.menu-row icon="layout-dashboard" label="My Dashboard" :href="route('app.profile.dashboard')" hint="Reading streak, prayers and learning" />
            <x-ui.menu-row icon="user-pen" label="Edit Profile" :href="route('app.settings.account')" />
            <x-ui.menu-row icon="wallet" label="Subscription & Wallet" :href="route('app.wallet.index')" />
            <x-ui.menu-row icon="users" label="Family Management" :href="route('app.family.index')" hint="Child profiles and parental controls" />
            <x-ui.menu-row icon="settings" label="Settings" :href="route('app.settings.index')" />
            <x-ui.menu-row icon="life-buoy" label="Help & Support" :href="route('app.support.index')" />
            <x-ui.menu-row icon="info" label="About SIRAJ" :href="route('app.about')" />
        </div>
        @if ($user->is_staff)<x-ui.button :href="route('admin.dashboard')" variant="outline" block icon="shield-check">Open admin portal</x-ui.button>@endif
        <form method="POST" action="{{ route('logout') }}">@csrf<x-ui.button type="submit" variant="gold" block>Log Out</x-ui.button></form>
    </div>
</x-layouts.app>
