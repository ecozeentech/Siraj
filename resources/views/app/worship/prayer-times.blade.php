@php
    $rows = ['fajr' => 'Fajr', 'sunrise' => 'Sunrise', 'dhuhr' => 'Dhuhr', 'asr' => 'Asr', 'maghrib' => 'Maghrib', 'isha' => 'Isha'];
@endphp
<x-layouts.base title="Prayer Times" body-class="bg-night-950" theme-color="#0a1520">
<div class="hero-night relative min-h-dvh pb-28 lg:pb-12 lg:ps-64">
    <div class="pointer-events-none absolute inset-x-0 top-24 h-72 overflow-hidden lg:start-64"><x-art.skyline :lights="true" class="absolute inset-x-0 bottom-0 h-full text-black/45" /></div>
    <div class="relative mx-auto max-w-xl px-5" style="padding-top: calc(1rem + var(--safe-top))">
        <div class="flex items-center gap-3">
            <a href="{{ route('app.worship.index') }}" class="grid size-10 place-items-center rounded-full bg-white/10 lg:hidden" aria-label="Back"><x-lucide-chevron-left class="size-5" /></a>
            <div class="min-w-0 flex-1"><h1 class="text-xl font-bold">Prayer Times</h1><p class="flex items-center gap-1 truncate text-sm text-white/70"><x-lucide-map-pin class="size-3.5" />{{ $pref->location_name }}</p></div>
            <a href="{{ route('app.worship.settings') }}" class="grid size-10 place-items-center rounded-full bg-white/10" aria-label="Prayer settings"><x-lucide-settings-2 class="size-5" /></a>
            <x-ui.bell />
        </div>

        <div class="mt-10 text-center">
            @if ($next)
                <p class="text-sm uppercase tracking-widest text-white/65">Next: {{ ucfirst($next['name']) }} · {{ $next['at']->format('H:i') }}</p>
                <p class="mt-2 font-display text-5xl font-bold tabular-nums sm:text-6xl" x-data="prayerCountdown({ target: @js($next['at']->toIso8601String()), serverNow: @js(now()->toIso8601String()) })" x-text="display">--:--:--</p>
            @else
                <p class="font-display text-3xl font-bold">{{ $day->format('l') }}</p>
            @endif
            <p class="mt-3 text-sm text-white/70">{{ $day->format('D, j M Y') }} · {{ $hijri }} AH</p>
        </div>

        <div class="mt-4 flex items-center justify-center gap-2 text-sm">
            <a href="{{ route('app.worship.prayer-times', ['date' => $day->subDay()->toDateString()]) }}" class="rounded-full bg-white/10 px-3 py-1.5 hover:bg-white/20" aria-label="Previous day"><x-lucide-chevron-left class="size-4" /></a>
            @unless ($isToday)<a href="{{ route('app.worship.prayer-times') }}" class="rounded-full bg-white/10 px-3 py-1.5 font-semibold">Today</a>@endunless
            <a href="{{ route('app.worship.prayer-times', ['date' => $day->addDay()->toDateString()]) }}" class="rounded-full bg-white/10 px-3 py-1.5 hover:bg-white/20" aria-label="Next day"><x-lucide-chevron-right class="size-4" /></a>
        </div>

        <ul class="mt-6 divide-y divide-white/10 overflow-hidden rounded-2xl bg-white/5 backdrop-blur-sm">
            @foreach ($rows as $key => $label)
                <li class="flex items-center justify-between px-5 py-3.5 {{ $current === $key ? 'bg-white/10 ring-1 ring-inset ring-gold-300/60' : '' }}">
                    <span class="flex items-center gap-2 text-lg {{ $current === $key ? 'font-bold text-gold-200' : '' }}">{{ $label }} @if ($current === $key)<span class="rounded-full bg-gold-300 px-2 py-0.5 text-[10px] font-bold uppercase text-forest-950">Now</span>@endif</span>
                    <span class="text-lg font-semibold tabular-nums {{ $current === $key ? 'text-gold-200' : '' }}">{{ $times[$key]->format('H:i') }}</span>
                </li>
            @endforeach
        </ul>
        <a href="{{ route('app.worship.timetable') }}" class="btn btn-gold btn-block mt-6">View Full Timetable <x-lucide-arrow-right class="size-4" /></a>
        <p class="mt-4 text-center text-xs text-white/55">Calculated with {{ $method }} · {{ $pref->asr_method === 'hanafi' ? 'Hanafi' : 'Standard' }} Asr. Local mosque times may differ by a few minutes.</p>
    </div>

    <nav class="safe-bottom fixed inset-x-0 bottom-0 z-50 border-t border-white/10 bg-night-950/95 backdrop-blur lg:hidden" aria-label="Primary">
        <ul class="mx-auto grid max-w-lg grid-cols-5">
            @foreach (\App\Support\Navigation::primary() as [$label, $route, $icon, $patterns])
                @php($active = \App\Support\Navigation::isActive($patterns))
                <li><a href="{{ route($route) }}" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-[11px] font-semibold {{ $active ? 'text-gold-300' : 'text-white/60' }}"><x-dynamic-component :component="'lucide-'.$icon" class="size-[22px]" />{{ $label }}</a></li>
            @endforeach
        </ul>
    </nav>
    <aside class="fixed inset-y-0 start-0 hidden w-64 flex-col overflow-y-auto bg-forest-950 lg:flex">
        <a href="{{ route('app.home') }}" class="px-5 pb-4 pt-6"><x-brand.logo size="sm" /></a>
        <nav class="space-y-1 px-3">@foreach (\App\Support\Navigation::primary() as [$label, $route, $icon, $patterns])<a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ \App\Support\Navigation::isActive($patterns) ? 'bg-white/10 text-gold-300' : 'text-white/75 hover:bg-white/5' }}"><x-dynamic-component :component="'lucide-'.$icon" class="size-[18px]" />{{ $label }}</a>@endforeach</nav>
    </aside>
</div>
</x-layouts.base>
