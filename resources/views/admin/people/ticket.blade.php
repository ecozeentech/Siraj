<x-layouts.admin :title="$ticket->subject">
    <a href="{{ route('admin.support.index') }}" class="text-sm font-semibold text-forest-700">All requests</a>
    <article class="rounded-2xl border border-line bg-white p-5 text-sm"><p class="text-xs text-muted">{{ $ticket->user?->name }} · {{ $ticket->created_at->diffForHumans() }} · {{ $ticket->priority }}</p><div class="prose-siraj mt-2">{!! \Illuminate\Support\Str::markdown(e($ticket->message), ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div></article>
    @foreach ($ticket->replies as $reply)
        <article class="rounded-2xl border border-line bg-white p-4 text-sm"><p class="text-xs text-muted">{{ $reply->is_staff ? 'Staff' : 'Member' }} · {{ $reply->user?->name }} · {{ $reply->created_at->diffForHumans() }}</p><p class="mt-1 whitespace-pre-wrap">{{ $reply->body }}</p></article>
    @endforeach
    <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="max-w-2xl space-y-3 rounded-2xl border border-line bg-white p-5">@csrf
        <x-ui.textarea name="body" label="Reply" rows="4" />
        <x-ui.select name="status" label="Set status" :options="['open'=>'Open','pending'=>'Pending','resolved'=>'Resolved','closed'=>'Closed']" :value="$ticket->status" />
        <x-ui.button type="submit">Send reply</x-ui.button>
    </form>
</x-layouts.admin>
