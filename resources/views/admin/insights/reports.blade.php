<x-layouts.admin title="Reports">
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['Members', number_format($members)], ['Joined in 7 days', number_format($newWeek)], ['Successful payments', \App\Support\Money::format($paid)], ['Active tickets', number_format($registrations)]] as [$label, $value])
            <div class="rounded-2xl border border-line bg-white p-4"><p class="text-2xl font-bold">{{ $value }}</p><p class="text-sm text-muted">{{ $label }}</p></div>
        @endforeach
    </div>
    <section class="rounded-2xl border border-line bg-white p-5 text-sm"><h2 class="font-bold">Payments by purpose</h2><ul class="mt-2 space-y-1">@forelse ($byPurpose as $row)<li class="flex justify-between"><span>{{ $row->purpose }} ({{ $row->c }})</span><span>{{ \App\Support\Money::format((int) $row->amount) }}</span></li>@empty<li class="text-muted">No successful payments yet.</li>@endforelse</ul></section>
    <div class="flex flex-wrap gap-2">
        <x-ui.button :href="route('admin.reports.export', 'members')" variant="outline" size="sm" icon="download">Members CSV</x-ui.button>
        <x-ui.button :href="route('admin.reports.export', 'payments')" variant="outline" size="sm" icon="download">Payments CSV</x-ui.button>
        <x-ui.button :href="route('admin.reports.export', 'registrations')" variant="outline" size="sm" icon="download">Registrations CSV</x-ui.button>
    </div>
</x-layouts.admin>
