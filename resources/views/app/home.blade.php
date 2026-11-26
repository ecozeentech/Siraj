@php
    $names = ['fajr' => 'Fajr', 'dhuhr' => 'Dhuhr', 'asr' => 'Asr', 'maghrib' => 'Maghrib', 'isha' => 'Isha'];
    $hour = (int) $now->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp
<x-layouts.app title="Home" flush headerless>
    <section class="hero-green pattern-geo relative overflow-hidden lg:rounded-b-[2rem]" style="padding-top: var(--safe-top)">
        <div class="mx-auto max-w-6xl px-4 pb-16 pt-5 lg:px-8">
            <div class="flex items-start gap-3">
                <div class="min-w-0 flex-1">
                    <h1 class="text-[22px] font-bold leading-tight">Assalamu Alaikum, {{ \Illuminate\Support\Str::of($user->name)->before(' ') }} <span aria-hidden="true">👋</span></h1>
                    <p class="mt-1 text-sm text-white/75">{{ $now->format('D, j M Y') }} <span class="mx-1 text-white/40">•</span> {{ $hijri }} AH</p>
                </div>
                <x-ui.bell />
                <a href="{{ route('app.profile.show') }}" aria-label="Profile"><x-ui.avatar :user="$user" size="sm" /></a>
            </div>

            {{-- Next prayer card (reference screen 2) --}}
            <div class="relative mt-5 overflow-hidden rounded-2xl border border-white/15 bg-forest-950/45 p-4 sm:p-5">
                <x-art.skyline :lights="true" class="pointer-events-none absolute -bottom-1 end-0 h-full w-3/5 text-forest-950/70" />
                <div class="relative" x-data="prayerCountdown({ target: @js($next['at']->toIso8601String()), serverNow: @js(now()->toIso8601String()) })">
                    <p class="text-xs font-medium uppercase tracking-widest text-white/65">Next prayer{{ $next['is_tomorrow'] ? ' · tomorrow' : '' }}</p>
                    <p class="mt-1 font-display text-3xl font-bold">{{ $names[$next['name']] }}</p>
                    <p class="text-lg font-semibold text-gold-200">{{ $next['at']->format('g:i A') }}</p>
                    <p class="mt-1 text-sm text-white/80">in <span class="font-semibold tabular-nums" x-text="display">--:--:--</span></p>
                    <a href="{{ route('app.worship.prayer-times') }}" class="mt-4 inline-flex min-h-10 items-center gap-1.5 rounded-full bg-white/15 px-4 text-sm font-semibold hover:bg-white/25">View Times <x-lucide-arrow-right class="size-4" /></a>
                    <p class="mt-3 flex items-center gap-1.5 text-xs text-white/60"><x-lucide-map-pin class="size-3.5" />{{ $pref->location_name }}</p>
                </div>
            </div>
        </div>
    </section>

    <div class="relative z-10 -mt-8 rounded-t-[1.75rem] bg-page lg:mx-auto lg:max-w-6xl lg:rounded-none lg:bg-transparent">
        <div class="space-y-6 px-4 pb-6 pt-5 lg:grid lg:grid-cols-3 lg:gap-6 lg:space-y-0 lg:px-8 lg:pt-8">
            <div class="space-y-6 lg:col-span-2">
                {{-- Today's prayers --}}
                <section aria-labelledby="today-prayers">
                    <x-ui.section-title title="Today's prayers" :href="route('app.worship.tracker')" link="Tracker" id="today-prayers" />
                    <div class="mt-3 grid grid-cols-5 gap-2">
                        @foreach ($names as $key => $label)
                            @php($log = $todayLog[$key] ?? null)
                            <a href="{{ route('app.worship.tracker') }}" class="card flex flex-col items-center gap-1 px-1 py-3 text-center {{ $current === $key ? 'ring-2 ring-emerald-brand' : '' }}">
                                <span class="text-[11px] font-semibold text-muted">{{ $label }}</span>
                                <span class="text-sm font-bold tabular-nums">{{ $times[$key]->format('g:i') }}</span>
                                <span class="grid size-5 place-items-center rounded-full {{ $log ? ($log->status === 'missed' ? 'bg-red-100 text-red-600' : 'bg-emerald-100 text-forest-700') : 'bg-surface-2 text-muted' }}">
                                    @if ($log && $log->status !== 'missed')<x-lucide-check class="size-3.5" />@elseif ($log)<x-lucide-x class="size-3.5" />@else<span class="size-1.5 rounded-full bg-current"></span>@endif
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>

                {{-- Continue reading --}}
                <section aria-labelledby="continue-reading">
                    <x-ui.section-title title="Continue Reading" :href="route('app.quran.index')" link="Quran" id="continue-reading" />
                    @if ($continue)
                        <a href="{{ route('app.quran.surah', ['surah' => $continue['surah']->id, 'ayah' => $continue['ayah']]) }}" class="card mt-3 flex items-center gap-4 p-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-mint text-forest-700 dark:text-emerald-100"><x-lucide-book-open class="size-6" /></span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-baseline justify-between gap-2"><span class="truncate font-semibold">{{ $continue['surah']->name_en }}</span><span class="font-quran text-xl">{{ $continue['surah']->name_ar }}</span></span>
                                <span class="text-xs text-muted">Ayah {{ $continue['ayah'] }} of {{ $continue['surah']->ayah_count }}</span>
                                <x-ui.progress class="mt-2" :value="$continue['percent']" label="Surah progress" />
                            </span>
                            <span class="btn btn-primary btn-sm shrink-0">Continue</span>
                        </a>
                    @else
                        <div class="card mt-3 flex items-center gap-4 p-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-mint text-forest-700"><x-lucide-book-open class="size-6" /></span>
                            <div class="min-w-0 flex-1"><p class="font-semibold">Begin with Al-Fatihah</p><p class="text-xs text-muted">Your place will be saved as you read.</p></div>
                            <a href="{{ route('app.quran.surah', 1) }}" class="btn btn-primary btn-sm">Start</a>
                        </div>
                    @endif
                </section>

                @if ($goal)
                    <section class="card flex items-center gap-4 p-4">
                        <span class="grid size-12 shrink-0 place-items-center rounded-full bg-gold-100 text-gold-600"><x-lucide-target class="size-6" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold">Daily Quran goal</p>
                            <p class="text-xs text-muted">{{ $goal['read_today'] }} of {{ $goal['goal']->daily_target }} ayahs today</p>
                            <x-ui.progress class="mt-2" tone="gold" :value="$goal['percent']" label="Daily goal" />
                        </div>
                        <a href="{{ route('app.quran.goals') }}" class="text-sm font-semibold text-forest-700 dark:text-emerald-500">Manage</a>
                    </section>
                @endif

                {{-- Quick tools --}}
                <section aria-labelledby="quick-tools">
                    <h2 id="quick-tools" class="text-base font-bold">Quick Tools</h2>
                    <div class="mt-3 grid grid-cols-4 gap-2.5">
                        <x-ui.tile icon="landmark" label="Prayer Tools" :href="route('app.worship.index')" />
                        <x-ui.tile icon="graduation-cap" label="Islamic Academy" tone="gold" :href="route('app.academy.index')" />
                        <x-ui.tile icon="baby" label="Kids Deen World" tone="pink" :href="route('app.kids.index')" />
                        <x-ui.tile icon="sparkles" label="SIRAJ AI" tone="teal" :href="route('app.ai.index')" />
                        <x-ui.tile icon="compass" label="Qibla" tone="blue" :href="route('app.worship.qibla')" />
                        <x-ui.tile icon="rotate-cw" label="Tasbih" tone="purple" :href="route('app.worship.tasbih')" />
                        <x-ui.tile icon="sun-moon" label="Adhkar" tone="gold" :href="route('app.worship.adhkar')" />
                        <x-ui.tile icon="calculator" label="Zakat" :href="route('app.zakat.index')" />
                    </div>
                </section>
            </div>

            <div class="space-y-6">
                <section aria-labelledby="upcoming">
                    <x-ui.section-title title="Upcoming Events" :href="route('app.events.index')" id="upcoming" />
                    <div class="mt-3 space-y-3">
                        @forelse ($events as $event)
                            <a href="{{ route('app.events.show', $event) }}" class="card flex gap-3 p-3">
                                <x-art.cover :theme="$event->cover_theme" :src="$event->cover_path ? \Illuminate\Support\Facades\Storage::url($event->cover_path) : null" class="size-16 shrink-0 rounded-xl" />
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-semibold">{{ $event->title }}</span>
                                    <span class="block text-xs text-muted">{{ $event->starts_at->setTimezone($event->timezone)->format('D, j M · g:i A') }}</span>
                                    <span class="mt-1 inline-flex items-center gap-1 text-xs text-muted"><x-lucide-map-pin class="size-3" />{{ $event->mode === 'online' ? 'Online' : ($event->venue_name ?: $event->city) }}</span>
                                </span>
                                <span class="self-center text-sm font-bold text-forest-700 dark:text-emerald-500">{{ $event->isFree() ? 'Free' : \App\Support\Money::format($event->price_kobo) }}</span>
                            </a>
                        @empty
                            <div class="card"><x-ui.empty icon="calendar-days" title="No upcoming events" text="New events from the community will appear here." class="py-8" /></div>
                        @endforelse
                    </div>
                </section>
                <section class="card flex items-center gap-3 p-4">
                    <span class="grid size-11 place-items-center rounded-full bg-mint text-forest-700 dark:text-emerald-100"><x-lucide-heart-handshake class="size-5" /></span>
                    <div class="min-w-0 flex-1"><p class="font-semibold">Morning &amp; evening adhkar</p><p class="text-xs text-muted">Keep your day anchored in remembrance.</p></div>
                    <a href="{{ route('app.worship.adhkar') }}" class="btn btn-outline btn-sm">Open</a>
                </section>
            </div>
        </div>
    </div>
</x-layouts.app>
