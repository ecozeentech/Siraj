<x-layouts.app title="Timetable" wide>
    <x-slot:header><x-ui.page-header :title="$month->format('F Y').' timetable'" :subtitle="$pref->location_name" :back="route('app.worship.prayer-times')" /></x-slot:header>
    <div class="space-y-4 py-5">
        <div class="flex items-center justify-between">
            <a href="{{ route('app.worship.timetable', ['month' => $month->subMonth()->format('Y-m')]) }}" class="btn btn-outline btn-sm"><x-lucide-chevron-left class="size-4" />Prev</a>
            <button type="button" class="btn btn-ghost btn-sm" onclick="window.print()"><x-lucide-printer class="size-4" />Print</button>
            <a href="{{ route('app.worship.timetable', ['month' => $month->addMonth()->format('Y-m')]) }}" class="btn btn-outline btn-sm">Next<x-lucide-chevron-right class="size-4" /></a>
        </div>
        <div class="card overflow-x-auto">
            <table class="w-full min-w-[34rem] text-center text-sm tabular-nums">
                <thead class="bg-surface-2 text-xs uppercase tracking-wide text-muted"><tr><th class="px-3 py-2.5 text-start">Day</th>@foreach (['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'] as $h)<th class="px-2 py-2.5">{{ $h }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-line">
                    @foreach ($rows as $r)
                        <tr class="{{ $r['date']->toDateString() === $today ? 'bg-mint font-semibold' : '' }} {{ $r['date']->isFriday() ? 'text-forest-700 dark:text-emerald-500' : '' }}">
                            <td class="px-3 py-2 text-start">{{ $r['date']->format('D j') }}</td>
                            @foreach (['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'] as $k)<td class="px-2 py-2">{{ $r['times'][$k]->format('H:i') }}</td>@endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
