<x-layouts.app title="{{ $person->name }}">
    <x-slot:header><x-ui.page-header :title="$person->name" :subtitle="$person->profile?->username ? '@'.$person->profile->username : 'Member'" :back="route('app.community.index')" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-4 py-5">
        <section class="card p-5 text-center">
            <x-ui.avatar :user="$person" size="xl" class="mx-auto" />
            <h2 class="mt-3 text-xl font-bold">{{ $person->name }}</h2>
            @if ($person->profile?->bio)<p class="mt-1 text-muted">{{ $person->profile->bio }}</p>@endif
            @if ($person->profile?->show_location && $person->profile?->city)<p class="mt-1 text-sm text-muted"><x-lucide-map-pin class="inline size-3.5" /> {{ $person->profile->city }}{{ $person->profile->country ? ', '.$person->profile->country : '' }}</p>@endif
            <p class="mt-3 text-sm"><strong>{{ $followers }}</strong> followers · <strong>{{ $following }}</strong> following</p>
            @if ($person->id !== auth()->id())
                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    <form method="POST" action="{{ route('app.community.follow', $person) }}">@csrf<x-ui.button type="submit" :variant="$isFollowing ? 'outline' : 'primary'">{{ $isFollowing ? 'Following' : 'Follow' }}</x-ui.button></form>
                    <form method="POST" action="{{ route('app.messages.start') }}">@csrf<input type="hidden" name="user_id" value="{{ $person->id }}"><x-ui.button type="submit" variant="outline" icon="message-circle">Message</x-ui.button></form>
                    <x-ui.button variant="ghost" @click="$dispatch('report', { url: @js(route('app.community.report', ['user', $person->id])), label: 'user' })" x-data>Report</x-ui.button>
                    <form method="POST" action="{{ route('app.community.block.store', $person) }}" onsubmit="return confirm('Block this user?')">@csrf<x-ui.button type="submit" variant="ghost">Block</x-ui.button></form>
                </div>
            @endif
        </section>
        @if ($canSee)
            @forelse ($posts as $post)<x-community.post-card :post="$post" :liked="$liked->contains($post->id)" />@empty<x-ui.empty icon="messages-square" title="No posts yet" />@endforelse
            {{ $posts->links() }}
        @else
            <x-ui.empty icon="lock" title="This profile is private" text="Follow this person to see their posts, if they allow it." />
        @endif
    </div>
    <x-community.report-sheet />
</x-layouts.app>
