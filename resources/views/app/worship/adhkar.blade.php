<x-layouts.app title="Adhkar">
    <x-slot:header>
        <x-ui.page-header title="Adhkar & Duas" subtitle="Remembrance for every moment" :back="route('app.worship.index')">
            <form method="GET" class="mx-auto mt-4 max-w-6xl" role="search"><input name="q" value="{{ $q }}" type="search" placeholder="Search categories" class="min-h-11 w-full rounded-full border-0 bg-white/15 px-5 text-sm text-white placeholder:text-white/65 focus:ring-2 focus:ring-gold-300"></form>
        </x-ui.page-header>
    </x-slot:header>
    <div class="space-y-5 py-5">
        <x-ui.alert type="warning" title="Content under review">Arabic text comes from the Hisn al-Muslim collection. English translations and hadith references are being added and reviewed before being marked verified.</x-ui.alert>
        @if ($featured->isNotEmpty())
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach ($featured as $c)
                    <a href="{{ route('app.worship.adhkar.show', $c->slug) }}" class="card flex items-center gap-3 p-4"><span class="grid size-11 place-items-center rounded-full bg-gold-100 text-gold-600"><x-dynamic-component :component="'lucide-'.($c->group === 'sleep' ? 'moon' : ($c->group === 'after_prayer' ? 'landmark' : 'sun'))" class="size-5" /></span><span class="min-w-0 flex-1"><span class="block truncate font-semibold">{{ $c->title() }}</span><span class="text-xs text-muted">{{ $c->entries_count }} adhkar</span></span></a>
                @endforeach
            </div>
        @endif
        <a href="{{ route('app.worship.adhkar.favorites') }}" class="card flex items-center gap-3 p-4"><x-lucide-heart class="size-5 text-pink-600" /><span class="flex-1 font-semibold">Favourites</span><span class="badge">{{ $favoritesCount }}</span></a>
        <div class="card divide-y divide-line overflow-hidden">
            @forelse ($cats as $c)
                <a href="{{ route('app.worship.adhkar.show', $c->slug) }}" class="list-row"><span class="min-w-0 flex-1"><span class="block truncate font-medium">{{ $c->title() }}</span><span class="block truncate font-quran text-sm text-muted" dir="rtl">{{ $c->title_ar }}</span></span><span class="text-xs text-muted">{{ $c->entries_count }}</span><x-lucide-chevron-right class="size-4 text-muted" /></a>
            @empty
                <x-ui.empty icon="search-x" title="No categories found" />
            @endforelse
        </div>
    </div>
</x-layouts.app>
