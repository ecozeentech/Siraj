<section class="rounded-2xl border border-line bg-white p-5">
    <h2 class="font-bold">Open listing reports</h2>
    <p class="mt-1 text-sm text-muted">Marking a report applied does not change the listing. Edit the mosque or business to correct it, and use the halal actions only when a certificate is on file.</p>
    <ul class="mt-3 divide-y divide-line text-sm">
        @forelse ($reports as $report)
            <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                <div><p class="font-medium">{{ ucfirst($report->reportable_type) }} #{{ $report->reportable_id }} · {{ str_replace('_', ' ', $report->type) }}</p><p class="text-muted">{{ $report->details }} · {{ $report->reporter?->name }}</p></div>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('admin.directories.reports', $report) }}">@csrf<input type="hidden" name="decision" value="applied"><button class="btn btn-outline btn-sm">Applied</button></form>
                    <form method="POST" action="{{ route('admin.directories.reports', $report) }}">@csrf<input type="hidden" name="decision" value="dismissed"><button class="btn btn-ghost btn-sm">Dismiss</button></form>
                </div>
            </li>
        @empty
            <li class="py-4 text-muted">No open directory reports.</li>
        @endforelse
    </ul>
</section>
