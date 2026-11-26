<x-layouts.app title="Worship">
    <x-slot:header>
        <x-ui.page-header title="Prayer & Worship" :subtitle="$hijri.' AH'">
            <x-slot:actions><x-ui.bell /></x-slot:actions>
            <a href="{{ route('app.worship.prayer-times') }}" class="mx-auto mt-4 flex max-w-6xl items-center gap-4 rounded-2xl border border-white/15 bg-forest-950/40 p-4" x-data="prayerCountdown({ target: @js($next['at']->toIso8601String()), serverNow: @js(now()->toIso8601String()) })">
                <span class="min-w-0 flex-1"><span class="block text-xs uppercase tracking-widest text-white/65">Next prayer</span><span class="font-display text-2xl font-bold">{{ $names[$next['name']] }}</span> <span class="ms-1 text-gold-200">{{ $next['at']->format('g:i A') }}</span></span>
                <span class="text-2xl font-bold tabular-nums" x-text="display">--:--:--</span>
            </a>
        </x-ui.page-header>
    </x-slot:header>
    <div class="space-y-6 py-5">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <x-ui.tile icon="clock" label="Prayer Times" :href="route('app.worship.prayer-times')" />
            <x-ui.tile icon="compass" label="Qibla" tone="blue" :href="route('app.worship.qibla')" />
            <x-ui.tile icon="sun-moon" label="Adhkar" tone="gold" :href="route('app.worship.adhkar')" />
            <x-ui.tile icon="rotate-cw" label="Tasbih" tone="purple" :href="route('app.worship.tasbih')" />
            <x-ui.tile icon="square-check-big" label="Prayer Tracker" tone="teal" :href="route('app.worship.tracker')" />
            <x-ui.tile icon="mic" label="Tajweed" tone="pink" :href="route('app.worship.tajweed')" />
            <x-ui.tile icon="compass" label="Hajj & Umrah" tone="gold" :href="route('app.hajj.index')" />
            <x-ui.tile icon="calculator" label="Zakat" :href="route('app.zakat.index')" />
        </div>
        <p class="flex items-center gap-2 text-sm text-muted"><x-lucide-map-pin class="size-4" />{{ $pref->location_name }} · <a href="{{ route('app.worship.settings') }}" class="font-semibold text-forest-700 dark:text-emerald-500">Change location & method</a></p>
    </div>
</x-layouts.app>
