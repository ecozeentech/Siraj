<x-layouts.admin title="Moderation">
    <div class="flex gap-2 text-sm"><a href="{{ route('admin.community.index') }}" class="chip {{ $tab === 'reports' ? 'chip-active' : '' }}">Reports</a><a href="{{ route('admin.community.index', ['tab' => 'appeals']) }}" class="chip {{ $tab === 'appeals' ? 'chip-active' : '' }}">Appeals</a></div>
    @if ($tab === 'reports')
        <div class="space-y-3">
            @forelse ($reports as $report)
                <article class="rounded-2xl border border-line bg-white p-4 text-sm">
                    <p class="font-medium">{{ ucfirst($report->reportable_type) }} #{{ $report->reportable_id }} · {{ str_replace('_', ' ', $report->reason) }} <span class="badge">{{ $report->status }}</span></p>
                    <p class="mt-1 text-muted">{{ $report->details }} · reported by {{ $report->reporter?->name ?? 'someone' }} {{ $report->created_at->diffForHumans() }}</p>
                    @if ($report->status === 'open')
                        <form method="POST" action="{{ route('admin.community.reports.update', $report) }}" class="mt-3 grid gap-2 sm:grid-cols-2">@csrf
                            <x-ui.select name="decision" label="Action" :options="['dismiss'=>'Dismiss','hide'=>'Hide','remove'=>'Remove','restore'=>'Restore','restrict'=>'Restrict the member','warn'=>'Warn the member']" />
                            <x-ui.select name="restriction" label="Restriction (if restricting)" :options="['posting'=>'Posting','messaging'=>'Messaging','all'=>'Posting and messaging']" placeholder="—" />
                            <x-ui.input name="days" type="number" label="Days (blank = until lifted)" />
                            <x-ui.input name="reason" label="Note to record" />
                            <x-ui.button type="submit" size="sm" class="sm:col-span-2 sm:w-fit">Apply</x-ui.button>
                        </form>
                    @endif
                </article>
            @empty
                <x-ui.empty icon="shield" title="No reports" text="The queue is empty." />
            @endforelse
            {{ $reports->links() }}
        </div>
    @else
        <div class="space-y-3">
            @forelse ($appeals as $appeal)
                <article class="rounded-2xl border border-line bg-white p-4 text-sm">
                    <p class="font-medium">{{ $appeal->user?->name }} <span class="badge">{{ $appeal->status }}</span></p>
                    <p class="mt-1 whitespace-pre-wrap">{{ $appeal->message }}</p>
                    @if ($appeal->status === 'open')
                        <form method="POST" action="{{ route('admin.community.appeals.update', $appeal) }}" class="mt-3 space-y-2">@csrf
                            <x-ui.select name="decision" label="Decision" :options="['upheld'=>'Keep the restriction','overturned'=>'Lift the restriction']" />
                            <x-ui.textarea name="response" label="Response the member will see" rows="3" />
                            <x-ui.button type="submit" size="sm">Save decision</x-ui.button>
                        </form>
                    @else
                        <p class="mt-2 text-muted">{{ $appeal->response }}</p>
                    @endif
                </article>
            @empty
                <x-ui.empty icon="shield" title="No appeals" text="No one is waiting on a review." />
            @endforelse
            {{ $appeals->links() }}
        </div>
    @endif
    @if ($aiReports->isNotEmpty())
        <section class="space-y-2"><h2 class="font-bold">Open AI reports</h2>
            @foreach ($aiReports as $report)
                <form method="POST" action="{{ route('admin.community.ai-reports.update', $report) }}" class="flex flex-wrap items-center justify-between gap-2 rounded-2xl border border-line bg-white p-3 text-sm">@csrf
                    <p>{{ $report->reason }} · {{ $report->user?->name }} · message #{{ $report->ai_message_id }}</p>
                    <div class="flex gap-2"><button name="status" value="reviewed" class="btn btn-outline btn-sm">Reviewed</button><button name="status" value="dismissed" class="btn btn-ghost btn-sm">Dismiss</button></div>
                </form>
            @endforeach
        </section>
    @endif
</x-layouts.admin>
