<x-layouts.app title="{{ $category->title() }}">
    <x-slot:header><x-ui.page-header :title="$category->title()" :subtitle="$category->title_ar" :back="route('app.worship.adhkar')" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-4 py-5">
        @foreach ($entries as $e)
            <article class="card p-4" x-data="adhkarCounter({ entryId: {{ $e->id }}, repeat: {{ $e->repeat_count }}, initial: {{ (int) ($progress[$e->id] ?? 0) }}, day: '{{ $day }}' })" x-data-fav="{{ isset($favs[$e->id]) ? 1 : 0 }}">
                <p class="quran-text text-[26px]" dir="rtl" lang="ar">{{ $e->text_ar }}</p>
                @if ($e->transliteration)<p class="mt-3 text-sm italic text-muted">{{ $e->transliteration }}</p>@endif
                @if ($e->translation_en)<p class="mt-3 text-[15px] leading-7">{{ $e->translation_en }}</p>@else<p class="mt-3 text-xs text-muted">English translation pending review.</p>@endif
                @if ($e->reference)<p class="mt-2 text-xs text-muted">Source: {{ $e->reference }}</p>@endif
                <div class="mt-4 flex items-center gap-3">
                    <button type="button" class="relative grid size-16 shrink-0 place-items-center rounded-full text-lg font-bold transition active:scale-95" :class="done ? 'bg-emerald-100 text-forest-800' : 'bg-forest-700 text-white'" @click="tap()" :aria-label="'Count: ' + count + ' of ' + repeat"><span x-show="!done" x-text="count + '/' + repeat"></span><x-lucide-check class="size-7" x-show="done" x-cloak /></button>
                    <div class="flex-1 text-sm text-muted">Repeat <strong class="text-ink">{{ $e->repeat_count }}×</strong><br><button type="button" class="text-xs font-semibold underline-offset-2 hover:underline" x-show="count > 0" @click="reset()">Reset</button></div>
                    <button type="button" class="grid size-10 place-items-center rounded-full text-muted hover:bg-surface-2" x-data="{ fav: {{ isset($favs[$e->id]) ? 'true' : 'false' }} }" @click="fetch('{{ route('app.worship.adhkar.favorite', $e) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, Accept: 'application/json' } }).then(r => r.json()).then(d => { fav = d.favorite; window.toast(fav ? 'Added to favourites' : 'Removed') }).catch(() => window.toast('Connect to the internet to update favourites', 'warning'))" aria-label="Toggle favourite"><x-lucide-heart class="size-5" x-bind:fill="fav ? '#db2777' : 'none'" x-bind:class="fav ? 'text-pink-600' : ''" /></button>
                </div>
            </article>
        @endforeach
    </div>
</x-layouts.app>
