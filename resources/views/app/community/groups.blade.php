<x-layouts.app title="Groups">
    <x-slot:header><x-ui.page-header title="Groups" subtitle="Study circles and communities" :back="route('app.community.index')" /></x-slot:header>
    <div class="mx-auto max-w-3xl space-y-4 py-5">
        @if ($canCreate)
            <details class="card p-4"><summary class="cursor-pointer font-semibold">Create a group</summary>
                <form method="POST" action="{{ route('app.community.groups.store') }}" class="mt-3 space-y-3">@csrf
                    <x-ui.input name="name" label="Group name" required maxlength="80" />
                    <x-ui.textarea name="description" label="Description" rows="2" maxlength="500" />
                    <x-ui.select name="privacy" label="Privacy" :options="['public' => 'Public: anyone can join', 'private' => 'Private: members approved by an admin']" />
                    <x-ui.button type="submit">Create group</x-ui.button>
                </form></details>
        @endif
        <div class="grid gap-3 sm:grid-cols-2">
            @forelse ($groups as $g)
                <a href="{{ route('app.community.groups.show', $g) }}" class="card block p-4 transition hover:shadow-md"><div class="flex items-start justify-between gap-2"><h2 class="font-bold">{{ $g->name }}</h2><span class="badge">{{ ucfirst($g->privacy) }}</span></div><p class="mt-1 line-clamp-2 text-sm text-muted">{{ $g->description }}</p><p class="mt-3 text-xs text-muted">{{ number_format($g->members_count) }} members · {{ $g->posts_count }} posts @if (($mine[$g->id] ?? null) === 'approved') · <span class="font-semibold text-forest-700">Joined</span>@elseif (($mine[$g->id] ?? null) === 'pending') · Pending @endif</p></a>
            @empty
                <div class="sm:col-span-2"><x-ui.empty icon="users-round" title="No groups yet" text="Create the first one." /></div>
            @endforelse
        </div>
        {{ $groups->links() }}
    </div>
</x-layouts.app>
