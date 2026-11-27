@php
    $names = ['fajr' => 'Fajr', 'dhuhr' => 'Dhuhr', 'asr' => 'Asr', 'maghrib' => 'Maghrib', 'isha' => 'Isha'];
    $first = \Illuminate\Support\Str::of($user->name)->before(' ')->toString();
    $event = $events->first();
    $dark = ($user->preferences?->theme ?? 'light') === 'dark';
@endphp
<x-layouts.app title="Home" flush headerless>
    <section class="relative overflow-hidden text-white" style="padding-top: var(--safe-top); background: radial-gradient(120% 80% at 80% 0%, #1f8a4c 0%, #0d6a38 42%, #064e2a 100%)">
        <div class="pattern-geo pointer-events-none absolute inset-x-0 top-0 h-40 opacity-80"></div>
        <div class="relative mx-auto max-w-md px-4 pb-1.5 pt-3">
            <div class="flex items-start gap-3">
                <div class="min-w-0 flex-1 pt-0.5">
                    <p class="text-[15px] font-medium leading-tight text-white">{{ __('Assalamu Alaikum,') }}</p>
                    <p class="text-[1.45rem] font-bold leading-tight">{{ $first }} <span aria-hidden="true">👋</span></p>
                    <p class="mt-1.5 flex items-center gap-1.5 text-[12px] text-white">
                        <x-lucide-calendar class="size-3.5 shrink-0" />
                        <span>{{ $now->format('D, j M Y') }} · {{ $hijri }}</span>
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <form method="POST" action="{{ route('app.theme') }}">@csrf
                        <input type="hidden" name="theme" value="{{ $dark ? 'light' : 'dark' }}">
                        <button type="submit" class="icon-pop grid size-11 place-items-center rounded-full bg-white/15 text-white" aria-label="{{ $dark ? 'Switch to light mode' : 'Switch to dark mode' }}">
                            @if ($dark)<x-lucide-sun class="size-5" />@else<x-lucide-moon class="size-5" />@endif
                        </button>
                    </form>
                    <x-ui.bell dot plain />
                    <a href="{{ route('app.profile.show') }}" class="icon-pop" aria-label="Profile"><x-ui.avatar :user="$user" size="md" class="ring-2 ring-white" /></a>
                </div>
            </div>

            <div class="relative mt-2 overflow-hidden rounded-[1.35rem] border border-white/20 bg-gradient-to-r from-[#0b5c30] via-[#0e6a36] to-[#1a7a3c] shadow-card" x-data="prayerCountdown({ target: @js($next['at']->toIso8601String()), serverNow: @js(now()->toIso8601String()) })">
                <img src="{{ asset('images/dash-prayer-mosque.png') }}" alt="" class="pointer-events-none absolute inset-y-0 right-0 h-full w-[56%] object-cover object-center">
                <div class="pointer-events-none absolute inset-y-0 right-0 w-[56%] bg-gradient-to-r from-[#0e6834] via-[#0e6834]/45 to-transparent"></div>
                <div class="relative px-4 py-3">
                    <p class="text-sm font-medium text-white/90">Next Prayer{{ $next['is_tomorrow'] ? ' · tomorrow' : '' }}</p>
                    <p class="mt-0.5 text-[1.75rem] font-bold leading-none tracking-tight">{{ $names[$next['name']] }}</p>
                    <p class="mt-0.5 text-[1.65rem] font-bold leading-none tabular-nums tracking-tight" x-text="display">--:--:--</p>
                    <a href="{{ route('app.worship.prayer-times') }}" class="mt-2.5 inline-flex min-h-11 items-center gap-1.5 rounded-full border border-white/80 px-4 text-sm font-semibold text-white">View Times <x-lucide-arrow-right class="size-4" /></a>
                </div>
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-md space-y-3 bg-page px-4 pb-2 pt-3">
        <section aria-labelledby="continue-reading">
            @php
                $continueHref = $continue
                    ? route('app.quran.surah', ['surah' => $continue['surah']->id, 'ayah' => $continue['ayah']])
                    : route('app.quran.surah', 1);
                $continueLabel = $continue
                    ? $continue['surah']->name_en.' • Ayah '.$continue['ayah']
                    : 'Al-Fatihah • Ayah 1';
                $continuePercent = $continue['percent'] ?? 0;
            @endphp
            <a href="{{ $continueHref }}" id="continue-reading" class="card flex items-center gap-3 p-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-emerald-100 text-[#128a49] dark:bg-forest-800 dark:text-emerald-300"><x-lucide-book-open class="size-6" /></span>
                <span class="min-w-0 flex-1">
                    <span class="block text-[15px] font-bold leading-tight">Continue Reading</span>
                    <span class="mt-0.5 block truncate text-[13px] text-muted">{{ $continueLabel }}</span>
                    <span class="mt-2 flex items-center gap-2">
                        <span class="h-1.5 min-w-0 flex-1 overflow-hidden rounded-full bg-emerald-100 dark:bg-forest-800" role="progressbar" aria-valuenow="{{ $continuePercent }}" aria-valuemin="0" aria-valuemax="100" aria-label="Surah progress">
                            <span class="block h-full rounded-full bg-[#128a49]" style="width: {{ $continuePercent }}%"></span>
                        </span>
                        <span class="text-xs font-semibold text-muted tabular-nums">{{ $continuePercent }}%</span>
                    </span>
                </span>
                <span class="inline-flex min-h-10 shrink-0 items-center rounded-full bg-[#128a49] px-4 text-sm font-semibold text-white">{{ $continue ? 'Continue' : 'Start' }}</span>
            </a>
        </section>

        <section aria-labelledby="quick-tools">
            <x-ui.section-title title="Quick Tools" :href="route('app.more')" id="quick-tools" />
            <div class="mt-2 grid grid-cols-4 gap-2">
                <x-ui.tile icon="landmark" label="Prayer Tools" :href="route('app.worship.index')" compact />
                <x-ui.tile icon="graduation-cap" label="Islamic Academy" tone="gold" :href="route('app.academy.index')" compact />
                <x-ui.tile icon="baby" label="Kids Deen World" tone="pink" :href="route('app.kids.index')" compact />
                <x-ui.tile icon="sparkles" label="SIRAJ AI" tone="teal" :href="route('app.ai.index')" compact />
                <x-ui.tile icon="compass" label="Qibla" tone="blue" :href="route('app.worship.qibla')" compact />
                <x-ui.tile icon="rotate-cw" label="Tasbih" tone="purple" :href="route('app.worship.tasbih')" compact />
                <x-ui.tile icon="sun-moon" label="Adhkar" tone="gold" :href="route('app.worship.adhkar')" compact />
                <x-ui.tile icon="calculator" label="Zakat" :href="route('app.zakat.index')" compact />
            </div>
        </section>

        <section aria-labelledby="upcoming">
            <x-ui.section-title title="Upcoming Events" :href="route('app.events.index')" id="upcoming" />
            <div class="mt-3">
                @if ($event)
                    @php($when = $event->starts_at->timezone($event->timezone ?: config('app.timezone')))
                    <a href="{{ route('app.events.show', $event) }}" class="card flex items-center gap-3 p-2.5">
                        <span class="relative size-16 shrink-0 overflow-hidden rounded-2xl">
                            <img src="{{ $event->cover_path ? \Illuminate\Support\Facades\Storage::url($event->cover_path) : asset('images/dash-event-cover.png') }}" alt="" class="size-full object-cover">
                            <span class="absolute start-1.5 top-1.5 grid h-9 w-9 place-items-center rounded-lg bg-[#128a49] text-center text-white leading-none">
                                <span class="text-[8px] font-bold tracking-wide">{{ strtoupper($when->format('D')) }}</span>
                                <span class="text-sm font-bold">{{ $when->format('j') }}</span>
                            </span>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-[15px] font-bold">{{ $event->title }}</span>
                            <span class="mt-1 flex items-center gap-1.5 text-xs text-muted"><x-lucide-clock class="size-3.5 shrink-0" />{{ $when->format('D, j M') }} • {{ $when->format('g:i A') }}</span>
                            <span class="mt-0.5 flex items-center gap-1.5 text-xs text-muted"><x-lucide-map-pin class="size-3.5 shrink-0" />{{ $event->mode === 'online' ? 'Online' : ($event->venue_name ?: $event->city ?: 'In person') }}</span>
                        </span>
                        <span class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-[#128a49] dark:bg-forest-800 dark:text-emerald-300">{{ $event->isFree() ? 'Free' : \App\Support\Money::format($event->price_kobo) }}</span>
                    </a>
                @else
                    <div class="card"><x-ui.empty icon="calendar-days" title="No upcoming events" text="New events from the community will appear here." class="py-8" /></div>
                @endif
            </div>
        </section>
    </div>
</x-layouts.app>
