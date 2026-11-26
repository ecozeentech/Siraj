<x-layouts.app title="Find people">
    <x-slot:header><x-ui.page-header title="Find people" subtitle="Follow and connect" :back="route('app.community.index')" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-4 py-5">
        <form method="GET" class="flex gap-2"><x-ui.input name="q" :value="$q" icon="search" placeholder="Search by name or @username" class="flex-1" /><x-ui.button type="submit">Search</x-ui.button></form>
        <div class="card divide-y divide-line">
            @forelse ($people as $p)
                <div class="flex items-center gap-3 p-3">
                    <a href="{{ route('app.community.profile', $p) }}" class="flex min-w-0 flex-1 items-center gap-3"><x-ui.avatar :user="$p" /><span class="min-w-0"><span class="block truncate font-semibold">{{ $p->name }}</span><span class="block truncate text-xs text-muted">{{ $p->profile?->username ? '@'.$p->profile->username : '' }}{{ $p->profile?->show_location && $p->profile?->city ? ' · '.$p->profile->city : '' }}</span></span></a>
                    <form method="POST" action="{{ route('app.community.follow', $p) }}">@csrf<x-ui.button type="submit" size="sm" :variant="in_array($p->id, $following) ? 'outline' : 'primary'">{{ in_array($p->id, $following) ? 'Following' : 'Follow' }}</x-ui.button></form>
                </div>
            @empty
                <x-ui.empty icon="users" title="No one found" text="Try a different name." />
            @endforelse
        </div>
        {{ $people->links() }}
    </div>
</x-layouts.app>
