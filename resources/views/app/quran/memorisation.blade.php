<x-layouts.app title="Memorisation">
    <x-slot:header><x-ui.page-header title="Memorisation" :subtitle="$total.' ayahs memorised'" :back="route('app.quran.index')" /></x-slot:header>
    <div class="space-y-3 py-5">
        @forelse ($surahs as $row)
            <a href="{{ route('app.quran.surah', ['surah' => $row['surah']->id, 'mode' => 'continuous']) }}" class="card block p-4">
                <span class="flex items-center justify-between"><span class="font-semibold">{{ $row['surah']->name_en }}</span><span class="text-xs text-muted">{{ $row['memorised'] }} / {{ $row['surah']->ayah_count }} memorised</span></span>
                <x-ui.progress class="mt-2" :value="$row['memorised'] / $row['surah']->ayah_count * 100" />
                <span class="mt-2 flex gap-3 text-xs text-muted"><span>{{ $row['learning'] }} learning</span><span>{{ $row['revising'] }} revising</span></span>
            </a>
        @empty
            <x-ui.empty icon="brain" title="Start your hifz journey" text="While reading, open More and mark an ayah as learning or memorised. It will be tracked here."><x-ui.button :href="route('app.quran.index')">Open the Quran</x-ui.button></x-ui.empty>
        @endforelse
    </div>
</x-layouts.app>
