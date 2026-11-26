<x-layouts.app :title="$group->name">
    <x-slot:header><x-ui.page-header :title="$group->name" :subtitle="number_format($group->members_count).' members · '.ucfirst($group->privacy)" :back="route('app.community.groups.index')" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-4 py-5">
        <section class="card p-4">
            @if ($group->description)<p>{{ $group->description }}</p>@endif
            <div class="mt-3 flex flex-wrap gap-2">
                @if (! $membership)
                    <form method="POST" action="{{ route('app.community.groups.join', $group) }}">@csrf<x-ui.button type="submit">{{ $group->privacy === 'public' ? 'Join group' : 'Request to join' }}</x-ui.button></form>
                @elseif ($membership->status === 'pending')
                    <span class="badge">Request pending</span>
                @endif
                @if ($membership && $group->owner_id !== auth()->id())
                    <x-ui.confirm :action="route('app.community.groups.leave', $group)" method="DELETE" message="Leave this group?" label="Leave" variant="outline" />
                @endif
            </div>
        </section>
        @foreach ($pending as $p)
            <div class="card flex items-center gap-3 p-3"><x-ui.avatar :user="$p->user" size="sm" /><span class="flex-1 font-medium">{{ $p->user->name }} wants to join</span><form method="POST" action="{{ route('app.community.groups.approve', [$group, $p]) }}">@csrf<x-ui.button type="submit" size="sm">Approve</x-ui.button></form></div>
        @endforeach
        @if ($approved)
            <form method="POST" action="{{ route('app.community.posts.store') }}" class="card space-y-3 p-4">@csrf<input type="hidden" name="group_id" value="{{ $group->id }}"><input type="hidden" name="visibility" value="public">
                <textarea name="body" rows="2" required maxlength="2000" class="field" placeholder="Post to this group..." aria-label="Post to group"></textarea><x-ui.button type="submit" size="sm">Post</x-ui.button></form>
        @endif
        @if ($canRead)
            @forelse ($posts as $post)<x-community.post-card :post="$post" :liked="$liked->contains($post->id)" />@empty<x-ui.empty icon="messages-square" title="No posts yet" />@endforelse
            {{ $posts->links() }}
        @else
            <x-ui.empty icon="lock" title="This is a private group" text="Request to join to see its posts." />
        @endif
    </div>
    <x-community.report-sheet />
</x-layouts.app>
