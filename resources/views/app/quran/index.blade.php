<x-layouts.app title="Quran">
    <x-slot:header>
        <x-ui.page-header title="Quran & Tafsir" subtitle="Read, listen and reflect">
            <x-slot:actions><a href="{{ route('app.quran.bookmarks') }}" class="grid size-10 place-items-center rounded-full bg-white/10" aria-label="Bookmarks"><x-lucide-bookmark class="size-5" /></a></x-slot:actions>
            <form method="GET" action="{{ route('app.quran.search') }}" class="mx-auto mt-4 max-w-6xl" role="search">
                <div class="relative"><x-lucide-search class="pointer-events-none absolute start-4 top-1/2 size-4 -translate-y-1/2 text-white/70" /><input name="q" type="search" placeholder="Search a surah, a topic, or a reference like 2:255" class="min-h-11 w-full rounded-full border-0 bg-white/15 ps-11 pe-4 text-sm text-white placeholder:text-white/65 focus:bg-white/25 focus:ring-2 focus:ring-gold-300"></div>
            </form>
        </x-ui.page-header>
    </x-slot:header>
    <div class="space-y-5 py-5">
        <div class="grid gap-3 lg:grid-cols-3">
            @if ($last)
                <a href="{{ route('app.quran.surah', ['surah' => $last['surah']->id, 'ayah' => $last['ayah']]) }}" class="card flex items-center gap-3 p-4 lg:col-span-2"><span class="grid size-11 place-items-center rounded-full bg-mint text-forest-700"><x-lucide-book-open-check class="size-5" /></span><span class="flex-1"><span class="block text-xs text-muted">Continue reading</span><span class="font-semibold">{{ $last['surah']->name_en }} · Ayah {{ $last['ayah'] }}</span></span><x-lucide-arrow-right class="size-5 text-muted" /></a>
            @else
                <a href="{{ route('app.quran.surah', 1) }}" class="card flex items-center gap-3 p-4 lg:col-span-2"><span class="grid size-11 place-items-center rounded-full bg-mint text-forest-700"><x-lucide-book-open class="size-5" /></span><span class="flex-1"><span class="block font-semibold">Start with Al-Fatihah</span><span class="text-xs text-muted">Your place is saved as you read.</span></span><x-lucide-arrow-right class="size-5 text-muted" /></a>
            @endif
            <a href="{{ route('app.quran.goals') }}" class="card flex items-center gap-3 p-4"><span class="grid size-11 place-items-center rounded-full bg-gold-100 text-gold-600"><x-lucide-flame class="size-5" /></span><span class="flex-1"><span class="block font-semibold">{{ $streak }}-day streak</span><span class="text-xs text-muted">{{ $goal ? $goal['read_today'].'/'.$goal['goal']->daily_target.' ayahs today' : 'Set a daily goal' }}</span></span></a>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button :href="route('app.quran.memorisation')" variant="outline" size="sm" icon="brain">Memorisation</x-ui.button>
            <x-ui.button :href="route('app.quran.offline')" variant="outline" size="sm" icon="download">Offline Quran</x-ui.button>
            <x-ui.button :href="route('app.worship.tajweed')" variant="outline" size="sm" icon="mic">Tajweed</x-ui.button>
        </div>
        <div class="flex gap-1 rounded-full bg-surface-2 p-1 text-sm font-semibold" role="tablist">
            <a role="tab" aria-selected="{{ $tab === 'surah' ? 'true' : 'false' }}" href="{{ route('app.quran.index') }}" class="flex-1 rounded-full py-2 text-center {{ $tab === 'surah' ? 'bg-surface shadow-card' : 'text-muted' }}">Surahs</a>
            <a role="tab" aria-selected="{{ $tab === 'juz' ? 'true' : 'false' }}" href="{{ route('app.quran.index', ['tab' => 'juz']) }}" class="flex-1 rounded-full py-2 text-center {{ $tab === 'juz' ? 'bg-surface shadow-card' : 'text-muted' }}">Juz</a>
        </div>
        @if ($tab === 'surah')
            <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($surahs as $s)
                    <a href="{{ route('app.quran.surah', $s->id) }}" class="card flex items-center gap-3 p-3.5 transition hover:shadow-md">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-mint text-sm font-bold text-forest-700 dark:text-emerald-100">{{ $s->id }}</span>
                        <span class="min-w-0 flex-1"><span class="block truncate font-semibold">{{ $s->name_en }}</span><span class="block truncate text-xs text-muted">{{ $s->meaning_en }} · {{ $s->ayah_count }} ayahs · {{ ucfirst($s->revelation) }}</span></span>
                        <span class="font-quran text-2xl text-forest-700 dark:text-emerald-100">{{ $s->name_ar }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($juz as $j)
                    <a href="{{ route('app.quran.juz', $j->id) }}" class="card flex items-center gap-3 p-3.5"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-gold-100 text-sm font-bold text-gold-600">{{ $j->id }}</span><span class="min-w-0 flex-1"><span class="block font-semibold">Juz {{ $j->id }}</span><span class="block truncate text-xs text-muted">From {{ $j->firstAyah->surah->name_en }} {{ $j->firstAyah->surah_id }}:{{ $j->firstAyah->number_in_surah }}</span></span></a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
