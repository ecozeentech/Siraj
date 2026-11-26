<x-layouts.app title="Quran preferences">
    <x-slot:header><x-ui.page-header title="Quran reading" :back="route('app.settings.index')" /></x-slot:header>
    <form method="POST" action="{{ route('app.settings.quran.update') }}" class="mx-auto max-w-xl space-y-4 py-5">@csrf @method('PUT')
        <div class="card space-y-4 p-4">
            <x-ui.select name="quran_reading_mode" label="Reading mode" :options="['ayah' => 'One ayah at a time', 'continuous' => 'Continuous', 'page' => 'By page (reference pagination)']" :value="$prefs->quran_reading_mode" />
            <x-ui.select name="translation_source_id" label="Translation" placeholder="None" :options="$translations->pluck('name', 'id')->all()" :value="$prefs->translation_source_id" />
            <x-ui.select name="tafsir_source_id" label="Tafsir" placeholder="None" :options="$tafsirs->pluck('name', 'id')->all()" :value="$prefs->tafsir_source_id" />
            <x-ui.select name="reciter_id" label="Reciter" placeholder="Default" :options="$reciters->pluck('name', 'id')->all()" :value="$prefs->reciter_id" />
            <div x-data="{ size: {{ (int) $prefs->quran_font_size }} }">
                <label class="field-label" for="size">Arabic text size: <span x-text="size"></span>px</label>
                <input id="size" type="range" name="quran_font_size" min="22" max="56" x-model="size" class="w-full accent-forest-700">
                <p class="quran-text mt-2 text-center" dir="rtl" :style="`font-size:${size}px`">بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ</p>
            </div>
            <x-ui.checkbox name="show_translation" label="Show translation" :checked="$prefs->show_translation" />
            <x-ui.checkbox name="show_tafsir" label="Show tafsir" :checked="$prefs->show_tafsir" />
        </div>
        <x-ui.button type="submit" block>Save</x-ui.button>
    </form>
</x-layouts.app>
