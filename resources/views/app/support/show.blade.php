<x-layouts.app title="Support request">
    <x-slot:header><x-ui.page-header :title="$ticket->subject" :subtitle="ucfirst($ticket->status)" :back="route('app.support.index')" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-3 py-5">
        <div class="card p-4"><p class="text-xs text-muted">You · {{ $ticket->created_at->diffForHumans() }}</p><p class="mt-1 whitespace-pre-line text-sm">{{ $ticket->message }}</p></div>
        @foreach ($ticket->replies as $r)
            <div class="card p-4 {{ $r->is_staff ? 'border-emerald-500/40 bg-mint' : '' }}"><p class="text-xs text-muted">{{ $r->is_staff ? 'SIRAJ Support' : 'You' }} · {{ $r->created_at->diffForHumans() }}</p><p class="mt-1 whitespace-pre-line text-sm">{{ $r->body }}</p></div>
        @endforeach
        @if ($ticket->status !== 'closed')
            <form method="POST" action="{{ route('app.support.reply', $ticket) }}" class="card space-y-3 p-4">@csrf<x-ui.textarea name="body" label="Reply" rows="3" required /><x-ui.button type="submit" block>Send reply</x-ui.button></form>
        @endif
    </div>
</x-layouts.app>
