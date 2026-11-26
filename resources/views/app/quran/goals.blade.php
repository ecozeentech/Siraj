<x-layouts.app title="Reading goal">
    <x-slot:header><x-ui.page-header title="Reading goal" subtitle="Small, steady steps" :back="route('app.quran.index')" /></x-slot:header>
    <div class="mx-auto max-w-xl space-y-4 py-5">
        <div class="grid grid-cols-2 gap-3">
            <div class="card p-4 text-center"><x-lucide-flame class="mx-auto size-6 text-gold-500" /><p class="mt-2 text-3xl font-bold">{{ $streak }}</p><p class="text-xs text-muted">day streak</p></div>
            <div class="card p-4 text-center"><x-lucide-target class="mx-auto size-6 text-forest-700" /><p class="mt-2 text-3xl font-bold">{{ $progress ? $progress['read_today'] : 0 }}</p><p class="text-xs text-muted">ayahs today</p></div>
        </div>
        @if ($progress)
            <div class="card p-4"><p class="font-semibold">Today: {{ $progress['read_today'] }} / {{ $progress['goal']->daily_target }} ayahs</p><x-ui.progress class="mt-3" tone="gold" :value="$progress['percent']" /></div>
        @endif
        <form method="POST" action="{{ route('app.quran.goals.save') }}" class="card space-y-4 p-4">@csrf
            <x-ui.input name="daily_target" type="number" min="1" max="500" label="Daily goal (ayahs)" :value="$progress['goal']->daily_target ?? 10" hint="For reference, one page is about 15 ayahs." />
            <x-ui.button type="submit" block>{{ $progress ? 'Update goal' : 'Set goal' }}</x-ui.button>
        </form>
        @if ($progress)<form method="POST" action="{{ route('app.quran.goals.clear') }}">@csrf @method('DELETE')<x-ui.button type="submit" variant="ghost" block>Remove goal</x-ui.button></form>@endif
    </div>
</x-layouts.app>
