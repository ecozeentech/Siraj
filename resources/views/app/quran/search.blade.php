<x-layouts.app title="Search Quran">
    <x-slot:header>
        <x-ui.page-header title="Search" :back="route('app.quran.index')">
            <form method="GET" action="{{ route('app.quran.search') }}" class="mx-auto mt-3 max-w-6xl" role="search"><input name="q" value="{{ $q }}" type="search" autofocus placeholder="Surah name, word, or 2:255" class="min-h-11 w-full rounded-full border-0 bg-white/15 px-5 text-sm text-white placeholder:text-white/65 focus:ring-2 focus:ring-gold-300"></form>
        </x-ui.page-header>
    </x-slot:header>
    <div class="space-y-5 py-5">
        @if ($note)<x-ui.alert>{{ $note }}</x-ui.alert>@endif
        @if ($surahMatches->isNotEmpty())
            <section><h2 class="mb-2 font-bold">Surahs</h2><div class="card divide-y divide-line overflow-hidden">@foreach ($surahMatches as $s)<a class="list-row" href="{{ route('app.quran.surah', $s->id) }}"><span class="badge">{{ $s->id }}</span><span class="flex-1 font-medium">{{ $s->name_en }}</span><span class="font-quran text-xl">{{ $s->name_ar }}</span></a>@endforeach</div></section>
        @endif
        @if ($results->isNotEmpty())
            <section><h2 class="mb-2 font-bold">Translation matches ({{ $results->count() }}{{ $results->count() === 40 ? '+' : '' }})</h2><div class="space-y-2">@foreach ($results as $r)<a href="{{ route('app.quran.surah', ['surah' => $r->surah_id, 'ayah' => $r->number_in_surah]) }}" class="card block p-3.5"><span class="text-xs font-bold text-forest-700 dark:text-emerald-500">{{ $r->name_en }} {{ $r->surah_id }}:{{ $r->number_in_surah }}</span><span class="mt-1 block text-sm leading-6">{{ $r->text }}</span></a>@endforeach</div></section>
        @elseif ($q !== '' && $surahMatches->isEmpty() && ! $note)
            <x-ui.empty icon="search-x" title="No results" text="Try a different word, a surah name, or a reference like 2:255." />
        @elseif ($q === '')
            <x-ui.empty icon="search" title="Search the Quran" text="Search by surah name, a word in the translation, or jump to a verse with 2:255." />
        @endif
    </div>
</x-layouts.app>
