<x-layouts.base title="Quran (offline)">
    <div class="mx-auto min-h-dvh max-w-2xl px-4 pb-16" x-data="offlineReader({ audioUrl: '/quran-audio/none/__ID__' })">
        <header class="sticky top-0 z-30 -mx-4 flex items-center gap-3 border-b border-line bg-surface/95 px-4 py-3 backdrop-blur">
            <a href="{{ route('pwa.offline') }}" class="grid size-9 place-items-center rounded-full hover:bg-surface-2" aria-label="Back"><x-lucide-chevron-left class="size-5" /></a>
            <h1 class="flex-1 font-bold" x-text="data ? data.surah.name_en : 'Offline Quran'"></h1>
            <button type="button" x-show="data" x-cloak class="btn btn-sm btn-outline" @click="close()">All surahs</button>
        </header>
        <template x-if="!data">
            <div class="py-4">
                <p x-show="surahs.length === 0" class="rounded-2xl bg-gold-100 p-4 text-sm text-[#6b5314]">No surahs are downloaded on this device yet. When you are online, open Quran &gt; Offline in the app and download the surahs you want.</p>
                <ul class="divide-y divide-line">
                    <template x-for="s in surahs" :key="s.id"><li><button type="button" class="flex w-full items-center gap-3 py-3 text-start" @click="open(s.id)"><span class="grid size-9 place-items-center rounded-full bg-mint text-sm font-bold text-forest-700" x-text="s.id"></span><span class="flex-1 font-semibold" x-text="s.name_en"></span><span class="quran-text !text-xl" dir="rtl" lang="ar" x-text="s.name_ar"></span></button></li></template>
                </ul>
            </div>
        </template>
        <template x-if="data">
            <div class="space-y-5 py-4">
                <div class="flex flex-wrap gap-2 text-sm"><label class="chip"><input type="checkbox" x-model="showTr" class="me-1">Translation</label><label class="chip" x-show="data.ayahs.some(a => a.tf)"><input type="checkbox" x-model="showTf" class="me-1">Tafsir</label><label class="chip">Size <input type="range" min="24" max="48" x-model.number="font" class="ms-2 w-24"></label></div>
                <p x-show="data.basmalah && data.surah.id !== 1 && data.surah.id !== 9" class="quran-text text-center" dir="rtl" lang="ar" x-text="data.basmalah"></p>
                <template x-for="a in data.ayahs" :key="a.id">
                    <article class="border-b border-line pb-5">
                        <div class="flex items-center justify-between text-xs text-muted"><span x-text="data.surah.id + ':' + a.n"></span><button type="button" @click="playingId === a.id ? stop() : play(a)" class="grid size-8 place-items-center rounded-full hover:bg-surface-2" aria-label="Play ayah"><x-lucide-play class="size-4" /></button></div>
                        <p class="quran-text mt-2" dir="rtl" lang="ar" :style="`font-size:${font}px`" x-text="a.ar"></p>
                        <p x-show="showTr && a.tr" class="mt-3 leading-7" x-text="a.tr"></p>
                        <p x-show="showTf && a.tf" class="mt-3 rounded-xl bg-surface-2 p-3 text-sm leading-7 text-muted" x-text="a.tf"></p>
                    </article>
                </template>
                <p class="text-xs text-muted" x-text="data.sources?.translation ? 'Translation: ' + data.sources.translation.name : ''"></p>
            </div>
        </template>
    </div>
</x-layouts.base>
