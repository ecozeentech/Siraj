<x-layouts.app title="Messages">
    <x-slot:header><x-ui.page-header title="Messages" subtitle="Private conversations" :back="route('app.more')" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-4 py-5">
        @if ($requests->isNotEmpty())
            <section><h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-muted">Message requests</h2>
                <div class="card divide-y divide-line">@foreach ($requests as $r)<a href="{{ route('app.messages.show', $r['c']) }}" class="flex items-center gap-3 p-3"><x-ui.avatar :user="$r['other']" /><span class="min-w-0 flex-1"><span class="block font-semibold">{{ $r['other']->name }}</span><span class="block truncate text-sm text-muted">{{ $r['last']?->body ?: 'Wants to message you' }}</span></span><span class="badge">Request</span></a>@endforeach</div></section>
        @endif
        <div class="card divide-y divide-line">
            @forelse ($rows as $r)
                <a href="{{ route('app.messages.show', $r['c']) }}" class="flex items-center gap-3 p-3 hover:bg-surface-2">
                    <x-ui.avatar :user="$r['other']" />
                    <span class="min-w-0 flex-1"><span class="flex items-center justify-between gap-2"><span class="truncate font-semibold">{{ $r['other']->name }}</span><span class="shrink-0 text-xs text-muted">{{ ($r['c']->last_message_at ?? $r['c']->created_at)->diffForHumans(null, true, true) }}</span></span>
                        <span class="block truncate text-sm {{ $r['unread'] ? 'font-semibold text-forest-950 dark:text-white' : 'text-muted' }}">@if ($r['last']){{ $r['last']->sender_id === auth()->id() ? 'You: ' : '' }}{{ $r['last']->body ?: 'Attachment' }}@else Say salam to start @endif</span></span>
                    @if ($r['unread'])<span class="grid min-w-6 place-items-center rounded-full bg-forest-700 px-1.5 text-xs font-bold text-white">{{ $r['unread'] }}</span>@endif
                </a>
            @empty
                <x-ui.empty icon="message-circle" title="No conversations yet" text="Find someone in the community to start a conversation."><x-ui.button :href="route('app.community.people')">Find people</x-ui.button></x-ui.empty>
            @endforelse
        </div>
    </div>
</x-layouts.app>
