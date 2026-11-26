<section class="rounded-2xl border border-line bg-white p-5">
    <h2 class="font-bold">Verification</h2>
    <p class="mt-1 text-sm text-muted">Re-checks counts, numbering, every ayah hash and the edition checksum. It never rewrites the text. Page numbers stay reference metadata; this is not a 604-page Mushaf certification.</p>
    @if ($edition)
        <p class="mt-3 text-sm">{{ $edition->name }} · {{ $edition->verified_at ? 'passed '.$edition->verified_at->diffForHumans() : 'not verified yet' }}</p>
        @if ($report = $edition->verification_report)
            <ul class="mt-3 space-y-1 text-sm">@foreach ($report['checks'] ?? [] as $check)<li class="{{ $check['passed'] ? 'text-forest-700' : 'text-red-700' }}">{{ $check['passed'] ? 'Pass' : 'Fail' }} — {{ $check['name'] }} ({{ $check['detail'] }})</li>@endforeach</ul>
        @endif
    @endif
    @can('quran.verify')
        <form method="POST" action="{{ route('admin.quran.verify') }}" class="mt-4">@csrf<x-ui.button type="submit" variant="outline" size="sm">Run verification</x-ui.button></form>
    @endcan
</section>
