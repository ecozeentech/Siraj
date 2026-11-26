<x-layouts.app title="My events">
    <x-slot:header><x-ui.page-header title="My events" subtitle="Your registrations and tickets" :back="route('app.events.index')" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-3 py-5">
        @forelse ($registrations as $r)
            <a href="{{ route('app.events.show', $r->event) }}" class="card flex items-center gap-4 p-4"><span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-mint text-center font-bold leading-tight text-forest-800"><span><span class="block text-lg">{{ $r->event->starts_at->format('d') }}</span><span class="text-xs">{{ $r->event->starts_at->format('M') }}</span></span></span><span class="min-w-0 flex-1"><span class="block truncate font-semibold">{{ $r->event->title }}</span><span class="block text-sm text-muted">{{ $r->event->starts_at->format('D j M, g:ia') }}</span></span><span class="text-end"><span class="badge {{ $r->status === 'registered' ? 'badge-check' : 'badge-gold' }}">{{ ucfirst(str_replace('_', ' ', $r->status)) }}</span><span class="mt-1 block font-mono text-xs text-muted">{{ $r->ticket_code }}</span></span></a>
        @empty
            <x-ui.empty icon="ticket" title="No registrations yet" text="Register for an event to see your ticket here."><x-ui.button :href="route('app.events.index')">Browse events</x-ui.button></x-ui.empty>
        @endforelse
    </div>
</x-layouts.app>
