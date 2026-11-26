<x-layouts.app title="Notifications">
    <x-slot:header>
        <x-ui.page-header title="Notifications" :back="route('app.more')">
            <x-slot:actions>
                @if ($notifications->total())
                    <form method="POST" action="{{ route('app.notifications.read-all') }}">@csrf<button class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold hover:bg-white/20">Mark all read</button></form>
                @endif
            </x-slot:actions>
        </x-ui.page-header>
    </x-slot:header>
    <div class="py-5">
        @forelse ($notifications as $n)
            <div class="card mb-2 flex items-start gap-3 p-3.5 {{ $n->read_at ? 'opacity-75' : '' }}">
                <span class="mt-0.5 grid size-9 shrink-0 place-items-center rounded-full {{ $n->read_at ? 'bg-surface-2 text-muted' : 'bg-mint text-forest-700' }}"><x-dynamic-component :component="'lucide-'.($n->data['icon'] ?? 'bell')" class="size-4" /></span>
                <form method="POST" action="{{ route('app.notifications.read', $n->id) }}" class="min-w-0 flex-1">@csrf
                    <button class="block w-full text-start">
                        <span class="block font-semibold">{{ $n->data['title'] ?? 'Notification' }}</span>
                        <span class="block text-sm text-muted">{{ $n->data['body'] ?? '' }}</span>
                        <span class="mt-1 block text-xs text-muted">{{ $n->created_at->diffForHumans() }}</span>
                    </button>
                </form>
                <form method="POST" action="{{ route('app.notifications.destroy', $n->id) }}">@csrf @method('DELETE')<button class="grid size-8 place-items-center rounded-full text-muted hover:bg-surface-2" aria-label="Dismiss"><x-lucide-x class="size-4" /></button></form>
            </div>
        @empty
            <x-ui.empty icon="bell-off" title="You're all caught up" text="Prayer reminders, replies and announcements will show up here." />
        @endforelse
        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>
</x-layouts.app>
