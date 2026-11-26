<x-layouts.base :title="$meta['title']" body-class="bg-page">
<div x-data="quranReader(@js($config))" class="mx-auto flex min-h-dvh max-w-3xl flex-col">
    <header class="sticky top-0 z-30 border-b border-line bg-page/95 backdrop-blur" style="padding-top: var(--safe-top)">
        <div class="flex h-14 items-center gap-2 px-3">
            <a href="{{ route('app.quran.index') }}" class="grid size-10 place-items-center rounded-full hover:bg-surface-2" aria-label="Back to Quran"><x-lucide-chevron-left class="size-5" /></a>
            <button type="button" class="mx-auto flex flex-col items-center leading-tight" @click="$dispatch('open-sheet', 'surahs')" aria-label="Choose surah">
                <span class="flex items-center gap-1 font-semibold">{{ $meta['title'] }}<x-lucide-chevron-down class="size-4 text-muted" /></span>
                <span class="text-xs text-muted" x-text="meta.kind === 'surah' ? 'Ayah ' + (current?.n ?? '') + '/' + total : 'Ayah ' + (current ? current.s + ':' + current.n : '')"></span>
            </button>
            <button type="button" class="grid size-10 place-items-center rounded-full hover:bg-surface-2" @click="$dispatch('open-sheet', 'reader-settings')" aria-label="Reading settings"><x-lucide-sliders-horizontal class="size-5" /></button>
        </div>
        <svg class="h-3 w-full text-gold-400/70" viewBox="0 0 400 12" preserveAspectRatio="none" aria-hidden="true"><defs><pattern id="orn" width="24" height="12" patternUnits="userSpaceOnUse"><path d="M12 1l5 5-5 5-5-5z" fill="none" stroke="currentColor" stroke-width="1"/><path d="M0 6h5M19 6h5" stroke="currentColor" stroke-width="1"/></pattern></defs><rect width="400" height="12" fill="url(#orn)"/></svg>
    </header>

    <main id="main" class="flex-1 px-5 pb-64 pt-2">
        {{-- Ayah mode (reference screen 3) --}}
        <section x-show="mode === 'ayah'" x-cloak>
            <article class="pt-6 text-center" x-show="current">
                <p x-show="current?.bm" class="quran-text mb-5 text-center text-gold-600 dark:text-gold-300" dir="rtl" lang="ar" :style="`font-size:${Math.round(font * 0.8)}px`" x-text="cfg.basmalah"></p>
                <p class="quran-text" dir="rtl" lang="ar" :style="`font-size:${font}px`" x-text="current?.ar"></p>
                <div class="mt-4 flex justify-center"><span class="ayah-marker" x-text="current?.n"></span></div>
                <p x-show="current?.sj" class="mx-auto mt-3 w-fit rounded-full bg-gold-100 px-3 py-1 text-xs font-semibold text-[#6b5314]">Sajdah ayah</p>
                <p x-show="showTr && current?.tr" class="mt-7 text-start text-lg leading-8" x-text="current?.tr"></p>
                <p x-show="showTr && !current?.tr && cfg.sources.translation" class="mt-7 text-start text-sm text-muted">No translation text for this ayah.</p>
                <div x-show="showTf && current?.tf" class="mt-6 rounded-2xl bg-mint p-4 text-start">
                    <p class="text-sm font-bold text-forest-700 dark:text-emerald-100" x-text="'Tafsir (' + (cfg.sources.tafsir?.name ?? '') + ')'"></p>
                    <p dir="auto" class="mt-1.5 text-[15px] leading-7" :class="tafsirOpen[current?.id] ? '' : 'line-clamp-3'" x-text="current?.tf"></p>
                    <button type="button" class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-forest-700 dark:text-emerald-100" @click="toggleTafsir(current)"><span x-text="tafsirOpen[current?.id] ? 'Show less' : 'Read More'"></span><x-lucide-arrow-right class="size-4" /></button>
                </div>
                <p x-show="notes[current?.id]" class="mt-4 rounded-xl border border-gold-300 bg-gold-100/60 p-3 text-start text-sm text-[#5a4511]"><span class="font-semibold">Your note:</span> <span x-text="notes[current?.id]"></span></p>
            </article>
        </section>

        {{-- Continuous mode --}}
        <section x-show="mode === 'continuous'" x-cloak class="pt-4">
            <p x-show="meta.kind === 'surah' && ayahs[0]?.bm" class="quran-text mb-6 text-center text-gold-600 dark:text-gold-300" dir="rtl" lang="ar" :style="`font-size:${Math.round(font * 0.8)}px`" x-text="cfg.basmalah"></p>
            <template x-for="(a, i) in ayahs" :key="a.id">
                <article :id="'ayah-' + a.id" data-ayah class="mb-4 rounded-2xl border-b border-line px-1 pb-5 pt-3 transition" :class="i === index ? 'bg-mint/50' : ''" @click="index = i">
                    <p x-show="meta.kind === 'juz' && a.n === 1" class="mb-2 text-center text-xs font-semibold uppercase tracking-widest text-gold-600" x-text="'Surah ' + cfg.surahs.find(s => s.id === a.s)?.name_en"></p>
                    <p class="quran-text text-center" dir="rtl" lang="ar" :style="`font-size:${font}px`"><span x-text="a.ar"></span> <span class="ayah-marker ayah-marker-sm" x-text="a.n"></span></p>
                    <p x-show="showTr && a.tr" class="mt-4 text-[17px] leading-8" x-text="a.tr"></p>
                    <div x-show="showTf && a.tf" class="mt-3 rounded-xl bg-mint p-3 text-sm"><p dir="auto" class="leading-6" :class="tafsirOpen[a.id] ? '' : 'line-clamp-2'" x-text="a.tf"></p><button type="button" class="mt-1 font-semibold text-forest-700 dark:text-emerald-100" @click.stop="toggleTafsir(a)" x-text="tafsirOpen[a.id] ? 'Show less' : 'Read more'"></button></div>
                    <p x-show="notes[a.id]" class="mt-3 rounded-xl border border-gold-300 bg-gold-100/60 p-2.5 text-sm text-[#5a4511]" x-text="'Note: ' + notes[a.id]"></p>
                    <div class="mt-3 flex items-center gap-1 text-muted">
                        <button type="button" class="grid size-9 place-items-center rounded-full hover:bg-surface-2" @click.stop="index = i; play(a)" aria-label="Play ayah"><x-lucide-play class="size-4" /></button>
                        <button type="button" class="grid size-9 place-items-center rounded-full hover:bg-surface-2" :class="bookmarks.has(a.id) ? 'text-gold-600' : ''" @click.stop="toggleBookmark(a)" aria-label="Bookmark ayah"><x-lucide-bookmark class="size-4" x-bind:fill="bookmarks.has(a.id) ? 'currentColor' : 'none'" /></button>
                        <button type="button" class="grid size-9 place-items-center rounded-full hover:bg-surface-2" @click.stop="openNote(a); $dispatch('open-sheet', 'note')" aria-label="Add note"><x-lucide-pencil-line class="size-4" /></button>
                        <button type="button" class="grid size-9 place-items-center rounded-full hover:bg-surface-2" @click.stop="index = i; $dispatch('open-sheet', 'more')" aria-label="More actions"><x-lucide-ellipsis class="size-4" /></button>
                    </div>
                </article>
            </template>
        </section>

        {{-- Page mode (reference pagination from the dataset) --}}
        <section x-show="mode === 'page'" x-cloak class="pt-4">
            <template x-if="pages[page]">
                <div>
                    <p class="mb-3 text-center text-xs font-semibold uppercase tracking-widest text-muted" x-text="'Page ' + pages[page].page"></p>
                    <p class="quran-text text-center" dir="rtl" lang="ar" :style="`font-size:${font}px; line-height: 2.3`"><template x-for="a in pages[page].list" :key="a.id"><span><span x-text="a.ar" :class="a.i === index ? 'rounded bg-mint' : ''" @click="index = a.i"></span> <span class="ayah-marker ayah-marker-sm" x-text="a.n"></span> </span></template></p>
                    <div x-show="showTr" class="mt-6 space-y-3 border-t border-line pt-4"><template x-for="a in pages[page].list" :key="'t' + a.id"><p class="text-[15px] leading-7"><span class="me-1 font-bold text-forest-700 dark:text-emerald-500" x-text="a.s + ':' + a.n"></span><span x-text="a.tr"></span></p></template></div>
                    <div class="mt-6 flex items-center justify-between"><button type="button" class="btn btn-outline btn-sm" :disabled="page === 0" @click="page--; index = pages[page].list[0].i; window.scrollTo({top:0})">Previous page</button><button type="button" class="btn btn-outline btn-sm" :disabled="page >= pages.length - 1" @click="page++; index = pages[page].list[0].i; window.scrollTo({top:0})">Next page</button></div>
                    <p class="mt-4 text-center text-xs text-muted">Pagination follows the dataset's reference page numbers and may differ from a printed Mushaf.</p>
                </div>
            </template>
        </section>

        <p class="mt-10 text-center text-xs text-muted" x-show="cfg.sources.translation || cfg.sources.tafsir">
            <span x-show="cfg.sources.translation">Translation: <span x-text="cfg.sources.translation?.name"></span>. </span><span x-show="cfg.sources.tafsir">Tafsir: <span x-text="cfg.sources.tafsir?.name"></span>.</span>
        </p>
    </main>

    {{-- Audio + actions (reference screen 3) --}}
    <div class="safe-bottom fixed inset-x-0 bottom-0 z-40 mx-auto max-w-3xl border-t border-line bg-surface/95 backdrop-blur">
        <div class="px-5 pt-3">
            <div class="h-1.5 w-full cursor-pointer rounded-full bg-surface-2" @click="seek($event)" role="slider" aria-label="Audio position" :aria-valuenow="Math.round(progressPct)" aria-valuemin="0" aria-valuemax="100"><div class="h-full rounded-full bg-forest-700 dark:bg-emerald-500" :style="`width:${progressPct}%`"></div></div>
            <div class="mt-1 flex justify-between text-[11px] tabular-nums text-muted"><span x-text="fmt(audio.time)"></span><span x-text="'-' + fmt(Math.max(0, audio.duration - audio.time))"></span></div>
            <p x-show="audio.error" x-text="audio.error" class="text-center text-xs text-red-600" role="alert"></p>
            <div class="flex items-center justify-between pb-1 pt-0.5">
                <button type="button" class="grid size-10 place-items-center rounded-full" :class="audio.repeat ? 'bg-mint text-forest-700' : 'text-muted'" @click="audio.repeat = !audio.repeat" aria-label="Repeat ayah" :aria-pressed="audio.repeat"><x-lucide-repeat-1 class="size-5" /></button>
                <button type="button" class="grid size-10 place-items-center rounded-full" @click="skip(-1)" aria-label="Previous ayah"><x-lucide-skip-back class="size-5" /></button>
                <button type="button" class="grid size-14 place-items-center rounded-full bg-forest-700 text-white shadow-card" @click="toggleAudio()" :aria-label="audio.playing ? 'Pause' : 'Play'"><x-lucide-play class="size-6" x-show="!audio.playing" /><x-lucide-pause class="size-6" x-show="audio.playing" x-cloak /></button>
                <button type="button" class="grid size-10 place-items-center rounded-full" @click="skip(1)" aria-label="Next ayah"><x-lucide-skip-forward class="size-5" /></button>
                <button type="button" class="grid size-10 place-items-center rounded-full text-xs font-bold text-muted" @click="cycleRate()" aria-label="Playback speed" x-text="audio.rate + 'x'"></button>
            </div>
        </div>
        <div class="grid grid-cols-4 border-t border-line">
            <button type="button" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-[11px] font-semibold" :class="current && bookmarks.has(current.id) ? 'text-gold-600' : 'text-muted'" @click="toggleBookmark(current)"><x-lucide-bookmark class="size-5" x-bind:fill="current && bookmarks.has(current.id) ? 'currentColor' : 'none'" />Bookmark</button>
            <button type="button" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-[11px] font-semibold text-muted" @click="openNote(current); $dispatch('open-sheet', 'note')"><x-lucide-pencil-line class="size-5" />Note</button>
            <button type="button" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-[11px] font-semibold text-muted" @click="$dispatch('open-sheet', 'translation')"><x-lucide-languages class="size-5" />Translation</button>
            <button type="button" class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-[11px] font-semibold text-muted" @click="$dispatch('open-sheet', 'more')"><x-lucide-ellipsis class="size-5" />More</button>
        </div>
    </div>

    {{-- Prev/next ayah (ayah mode) --}}
    <div x-show="mode === 'ayah'" x-cloak class="pointer-events-none fixed inset-x-0 bottom-64 z-30 mx-auto flex max-w-3xl justify-between px-3">
        <button type="button" class="pointer-events-auto grid size-11 place-items-center rounded-full bg-surface shadow-card disabled:opacity-30" :disabled="index === 0" @click="go(-1)" aria-label="Previous ayah"><x-lucide-chevron-left class="size-5" /></button>
        <button type="button" class="pointer-events-auto grid size-11 place-items-center rounded-full bg-surface shadow-card disabled:opacity-30" :disabled="index >= total - 1" @click="go(1)" aria-label="Next ayah"><x-lucide-chevron-right class="size-5" /></button>
    </div>

    <x-ui.sheet name="surahs" title="Go to">
        <div class="space-y-3">
            <div x-show="meta.kind === 'surah'"><label class="field-label" for="goto">Ayah number</label><div class="flex gap-2"><input id="goto" type="number" min="1" :max="total" class="field" x-ref="goto" placeholder="1 – {{ count($config['ayahs']) }}"><button type="button" class="btn btn-primary" @click="jump($refs.goto.value)">Go</button></div></div>
            <div class="max-h-72 divide-y divide-line overflow-y-auto rounded-xl border border-line">
                <template x-for="s in cfg.surahs" :key="s.id"><button type="button" class="flex w-full items-center gap-3 px-3 py-2.5 text-start hover:bg-surface-2" @click="jumpSurah(s.id)"><span class="grid size-8 place-items-center rounded-full bg-mint text-xs font-bold" x-text="s.id"></span><span class="flex-1 font-medium" x-text="s.name_en"></span><span class="font-quran text-lg" x-text="s.name_ar"></span></button></template>
            </div>
        </div>
    </x-ui.sheet>

    <x-ui.sheet name="reader-settings" title="Reading settings">
        <div class="space-y-5">
            <div><p class="field-label">Reading mode</p><div class="grid grid-cols-3 gap-2 rounded-xl bg-surface-2 p-1">
                @foreach (['ayah' => 'Ayah', 'continuous' => 'Continuous', 'page' => 'Page'] as $m => $l)<button type="button" class="min-h-10 rounded-lg text-sm font-semibold" :class="mode === '{{ $m }}' ? 'bg-surface shadow-card' : 'text-muted'" @click="mode = '{{ $m }}'">{{ $l }}</button>@endforeach
            </div></div>
            <div><p class="field-label">Arabic text size</p><div class="flex items-center gap-3"><button type="button" class="btn btn-outline btn-sm" @click="setFont(-2)" aria-label="Smaller">A−</button><span class="flex-1 text-center font-semibold tabular-nums" x-text="font + 'px'"></span><button type="button" class="btn btn-outline btn-sm" @click="setFont(2)" aria-label="Larger">A+</button></div></div>
            <label class="flex items-center justify-between"><span class="font-medium">Show translation</span><input type="checkbox" class="size-5 rounded border-line text-forest-700" :checked="showTr" @change="toggleTr()"></label>
            <label class="flex items-center justify-between"><span class="font-medium">Show tafsir</span><input type="checkbox" class="size-5 rounded border-line text-forest-700" :checked="showTf" @change="toggleTf()"></label>
            <a href="{{ route('app.settings.quran') }}" class="block text-center text-sm font-semibold text-forest-700 dark:text-emerald-500">More reading options</a>
        </div>
    </x-ui.sheet>

    <x-ui.sheet name="translation" title="Translation, tafsir & reciter">
        <form method="POST" action="{{ route('app.settings.quran.update') }}" class="space-y-4">@csrf @method('PUT')
            <input type="hidden" name="quran_font_size" :value="font"><input type="hidden" name="quran_reading_mode" :value="mode"><input type="hidden" name="show_translation" value="1"><input type="hidden" name="show_tafsir" value="1">
            <x-ui.select name="translation_source_id" label="Translation" placeholder="None" :options="$translations->pluck('name', 'id')->all()" :value="$prefs->translation_source_id" />
            <x-ui.select name="tafsir_source_id" label="Tafsir" placeholder="None" :options="$tafsirs->pluck('name', 'id')->all()" :value="$prefs->tafsir_source_id" />
            <x-ui.select name="reciter_id" label="Reciter" :options="$reciters->pluck('name', 'id')->all()" :value="$prefs->reciter_id" />
            <x-ui.button type="submit" block>Apply</x-ui.button>
        </form>
    </x-ui.sheet>

    <x-ui.sheet name="note" title="Note">
        <p class="mb-2 text-sm text-muted" x-text="note.ayah ? 'Ayah ' + note.ayah.s + ':' + note.ayah.n : ''"></p>
        <textarea x-ref="noteInput" x-model="note.text" rows="5" maxlength="2000" class="field" placeholder="Write a reflection or reminder (private to you)"></textarea>
        <x-ui.button type="button" block class="mt-3" x-on:click="saveNote(); $dispatch('close-sheet')">Save note</x-ui.button>
    </x-ui.sheet>

    <x-ui.sheet name="more" title="Ayah actions">
        <div class="divide-y divide-line rounded-xl border border-line text-[15px]">
            <button type="button" class="flex w-full items-center gap-3 px-4 py-3 text-start" @click="copy(current)"><x-lucide-copy class="size-4 text-muted" />Copy ayah</button>
            <button type="button" class="flex w-full items-center gap-3 px-4 py-3 text-start" @click="share(current)"><x-lucide-share-2 class="size-4 text-muted" />Share</button>
            <button type="button" class="flex w-full items-center gap-3 px-4 py-3 text-start" @click="setMemorised(current, 'learning')"><x-lucide-brain class="size-4 text-muted" />Mark as learning (hifz)</button>
            <button type="button" class="flex w-full items-center gap-3 px-4 py-3 text-start" @click="setMemorised(current, 'memorised')"><x-lucide-circle-check class="size-4 text-muted" />Mark as memorised</button>
            <button type="button" class="flex w-full items-center gap-3 px-4 py-3 text-start" x-show="current && memorised[current.id]" @click="setMemorised(current, 'none')"><x-lucide-circle-x class="size-4 text-muted" />Remove from memorisation</button>
        </div>
    </x-ui.sheet>
</div>
</x-layouts.base>
