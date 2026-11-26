<x-layouts.app title="My Dashboard">
    <x-slot:header><x-ui.page-header title="My Dashboard" subtitle="Your activity, privately yours" :back="route('app.profile.show')" /></x-slot:header>
    <div class="space-y-6 py-5">
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([['flame', 'Reading streak', $streak.' '.\Illuminate\Support\Str::plural('day', $streak)], ['book-open', 'Ayahs read', number_format($ayahsTotal)], ['landmark', 'Prayers logged (7d)', $prayersWeek.' / 35'], ['rotate-cw', 'Dhikr counted', number_format($dhikrTotal)]] as [$icon, $label, $val])
                <div class="card p-4"><span class="grid size-9 place-items-center rounded-full bg-mint text-forest-700 dark:text-emerald-100"><x-dynamic-component :component="'lucide-'.$icon" class="size-[18px]" /></span><p class="mt-3 text-2xl font-bold">{{ $val }}</p><p class="text-xs text-muted">{{ $label }}</p></div>
            @endforeach
        </div>
        <section class="card p-4">
            <h2 class="font-bold">Ayahs read this week</h2>
            @php($max = max(1, $chart->max('value')))
            <div class="mt-4 flex h-36 items-end gap-2" role="img" aria-label="Bar chart of ayahs read in the last seven days">
                @foreach ($chart as $c)
                    <div class="flex flex-1 flex-col items-center gap-1"><span class="text-[11px] font-semibold text-muted">{{ $c['value'] ?: '' }}</span><div class="w-full rounded-t-lg bg-forest-700 dark:bg-emerald-500" style="height: {{ max(4, $c['value'] / $max * 100) }}%; {{ $c['value'] ? '' : 'opacity:.2' }}"></div><span class="text-[11px] text-muted">{{ $c['label'] }}</span></div>
                @endforeach
            </div>
        </section>
        <section>
            <x-ui.section-title title="My courses" :href="route('app.academy.index')" />
            <div class="mt-3 space-y-2">
                @forelse ($courses as $e)
                    <a href="{{ route('app.academy.course', $e->course) }}" class="card flex items-center gap-3 p-3"><span class="min-w-0 flex-1"><span class="block truncate font-semibold">{{ $e->course->title }}</span><x-ui.progress class="mt-2" :value="$e->progress_percent" /></span><span class="text-sm font-bold">{{ $e->progress_percent }}%</span></a>
                @empty
                    <div class="card"><x-ui.empty icon="graduation-cap" title="No courses yet" text="Enrol in a course to track your progress here." class="py-8"><x-ui.button :href="route('app.academy.index')" size="sm">Browse courses</x-ui.button></x-ui.empty></div>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
