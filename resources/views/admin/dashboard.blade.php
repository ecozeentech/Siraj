<x-layouts.admin title="Dashboard">
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($stats as [$label, $count, $icon])
            <div class="flex items-center gap-4 rounded-2xl border border-line bg-white p-4"><span class="grid size-11 place-items-center rounded-xl bg-mint text-forest-700"><x-dynamic-component :component="'lucide-'.$icon" class="size-5" /></span><div><p class="text-2xl font-bold">{{ number_format($count) }}</p><p class="text-sm text-muted">{{ $label }}</p></div></div>
        @endforeach
    </div>
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-2xl border border-line bg-white p-5">
            <h2 class="font-bold">Money</h2>
            <dl class="mt-3 space-y-2 text-sm"><div class="flex justify-between"><dt class="text-muted">Successful payments today</dt><dd class="font-semibold">{{ \App\Support\Money::format($paidToday) }}</dd></div><div class="flex justify-between"><dt class="text-muted">Subscription and ticket revenue (ledger)</dt><dd class="font-semibold">{{ \App\Support\Money::format($revenue) }}</dd></div></dl>
            <p class="mt-3 text-xs text-muted">Revenue is the ledger balance, not a chart. Refunds reduce it.</p>
        </section>
        <section class="rounded-2xl border border-line bg-white p-5">
            <h2 class="font-bold">Quran text</h2>
            @if ($quran)
                <p class="mt-2 text-sm">{{ $quran->name }} · {{ number_format($quran->ayah_count) }} ayahs · checksum {{ substr((string) $quran->checksum, 0, 12) }}…</p>
                <p class="mt-1 text-sm {{ $quran->verified_at ? 'text-forest-700' : 'text-[#6b5314]' }}">{{ $quran->verified_at ? 'Last verification passed '.$quran->verified_at->diffForHumans().'.' : 'Not verified in this environment yet.' }}</p>
            @else
                <p class="mt-2 text-sm text-muted">No Quran edition has been imported.</p>
            @endif
            <p class="mt-2 text-xs text-muted">Page numbers are reference metadata. SIRAJ does not claim a verified 604-page Mushaf.</p>
        </section>
    </div>
    <section class="rounded-2xl border border-line bg-white p-5">
        <h2 class="font-bold">Latest audit events</h2>
        <ul class="mt-3 divide-y divide-line text-sm">
            @forelse ($audit as $log)
                <li class="flex flex-wrap justify-between gap-2 py-2"><span><span class="font-medium">{{ $log->action }}</span> <span class="text-muted">{{ $log->description }}</span></span><span class="text-xs text-muted">{{ $log->actor?->name ?? 'System' }} · {{ $log->created_at->diffForHumans() }}</span></li>
            @empty
                <li class="py-4 text-muted">No audit events yet.</li>
            @endforelse
        </ul>
    </section>
</x-layouts.admin>
