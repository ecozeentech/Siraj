<x-layouts.base title="Adhkar (offline)">
    <div class="mx-auto min-h-dvh max-w-2xl px-4 pb-16">
        <header class="sticky top-0 z-30 -mx-4 flex items-center gap-3 border-b border-line bg-surface/95 px-4 py-3 backdrop-blur"><a href="{{ route('pwa.offline') }}" class="grid size-9 place-items-center rounded-full hover:bg-surface-2" aria-label="Back"><x-lucide-chevron-left class="size-5" /></a><h1 class="flex-1 font-bold">Daily adhkar</h1></header>
        <p class="mt-4 rounded-2xl bg-gold-100 p-3 text-xs text-[#6b5314]">Arabic text from Hisn al-Muslim. English translations are shown where reviewed.</p>
        @foreach ($categories as $c)
            <section class="mt-6"><h2 class="text-lg font-bold">{{ $c->title() }}</h2>
                @foreach ($c->entries as $e)<article class="mt-3 rounded-2xl border border-line p-4"><p class="quran-text text-[26px]" dir="rtl" lang="ar">{{ $e->text_ar }}</p>@if ($e->translation_en)<p class="mt-3">{{ $e->translation_en }}</p>@endif<p class="mt-2 text-xs text-muted">Repeat {{ $e->repeat_count }}×@if ($e->reference) · {{ $e->reference }}@endif</p></article>@endforeach
            </section>
        @endforeach
    </div>
</x-layouts.base>
