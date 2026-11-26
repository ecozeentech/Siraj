<x-layouts.app title="Offline Quran">
    <x-slot:header><x-ui.page-header title="Offline Quran" subtitle="Read and listen without internet" :back="route('app.settings.index')" /></x-slot:header>
    <div class="space-y-4 py-5" x-data="offlineQuran({ translationId: {{ (int) $prefs->translation_source_id ?: 'null' }}, tafsirId: {{ (int) $prefs->tafsir_source_id ?: 'null' }}, reciter: @js($reciter?->slug), dataUrl: @js(url('/app/quran/data/__ID__')), audioUrl: @js(url('/quran-audio/'.($reciter?->slug ?? 'none').'/__ID__')) })">
        <x-ui.alert type="info" title="How it works">Downloaded surahs are stored on this device (text with your selected translation and tafsir, and optionally audio from {{ $reciter?->name ?? 'your reciter' }}). Open the offline reader at <a href="{{ route('pwa.offline.quran') }}" class="font-semibold underline">/offline/quran</a> any time, even with no connection.</x-ui.alert>
        <p class="text-xs text-muted" x-show="estimate" x-text="estimate"></p>
        <p class="text-sm font-medium text-red-600" x-show="message" x-text="message" role="alert"></p>
        <div class="card divide-y divide-line overflow-hidden">
            @foreach ($surahs as $s)
                @php($js = json_encode(['id' => $s->id, 'name_en' => $s->name_en]))
                <div class="flex items-center gap-3 p-3.5">
                    <span class="grid size-9 place-items-center rounded-lg bg-mint text-xs font-bold">{{ $s->id }}</span>
                    <div class="min-w-0 flex-1"><p class="truncate font-medium">{{ $s->name_en }}</p>
                        <p class="text-xs text-muted"><span x-show="!items[{{ $s->id }}]">{{ $s->ayah_count }} ayahs</span><span x-show="items[{{ $s->id }}]" x-cloak>Saved<span x-show="items[{{ $s->id }}]?.audio"> · audio saved</span></span></p>
                        <div x-show="busy === 'audio-{{ $s->id }}'" x-cloak class="mt-1.5 flex items-center gap-2"><div class="h-1.5 flex-1 overflow-hidden rounded-full bg-surface-2"><div class="h-full bg-forest-700" :style="`width:${progress}%`"></div></div><button type="button" class="text-xs font-semibold text-red-600" @click="cancel()">Cancel</button></div>
                    </div>
                    <template x-if="!items[{{ $s->id }}]"><button type="button" class="btn btn-outline btn-sm" :disabled="busy" @click="downloadText({{ $js }})">Text</button></template>
                    <template x-if="items[{{ $s->id }}] && !items[{{ $s->id }}].audio"><button type="button" class="btn btn-outline btn-sm" :disabled="busy" @click="downloadAudio({{ $js }})">+ Audio</button></template>
                    <template x-if="items[{{ $s->id }}]"><button type="button" class="grid size-9 place-items-center rounded-full text-muted hover:bg-surface-2" :disabled="busy" @click="remove({{ $js }})" aria-label="Remove {{ $s->name_en }} from this device"><x-lucide-trash-2 class="size-4" /></button></template>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
