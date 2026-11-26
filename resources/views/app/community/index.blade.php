<x-layouts.app title="Community">
    <x-slot:header>
        <x-ui.page-header title="Community" subtitle="Connect with the Ummah" :back="route('app.more')">
            <x-slot:actions><a href="{{ route('app.community.people') }}" class="grid size-10 place-items-center rounded-full bg-white/10 hover:bg-white/20" aria-label="Find people"><x-lucide-user-search class="size-5" /></a></x-slot:actions>
            <nav class="mx-auto mt-4 flex max-w-6xl gap-2 text-sm font-semibold" aria-label="Community sections">
                @foreach (['latest' => 'Feed', 'following' => 'Following', 'popular' => 'Popular'] as $k => $l)<a href="{{ route('app.community.index', ['tab' => $k]) }}" class="rounded-full px-4 py-1.5 {{ $tab === $k ? 'bg-white text-forest-800' : 'bg-white/10 text-white' }}">{{ $l }}</a>@endforeach
                <a href="{{ route('app.community.groups.index') }}" class="rounded-full bg-white/10 px-4 py-1.5">Groups</a>
            </nav>
        </x-ui.page-header>
    </x-slot:header>
    <div class="mx-auto max-w-2xl space-y-4 py-5">
        @if ($restriction)
            <x-ui.alert type="warning" title="Your account is restricted">{{ $restriction->reason ?: 'A moderator has restricted some community features.' }} @if ($restriction->expires_at) This ends {{ $restriction->expires_at->diffForHumans() }}. @endif
                <form method="POST" action="{{ route('app.community.appeal') }}" class="mt-2 space-y-2">@csrf<x-ui.textarea name="message" rows="2" placeholder="Explain why this should be reviewed" /><x-ui.button type="submit" size="sm">Appeal</x-ui.button></form></x-ui.alert>
        @else
            <form method="POST" action="{{ route('app.community.posts.store') }}" enctype="multipart/form-data" class="card space-y-3 p-4" x-data="{ n: 0 }">
                @csrf
                <div class="flex gap-3"><x-ui.avatar :user="auth()->user()" size="sm" /><textarea name="body" rows="2" maxlength="2000" class="field min-h-16 flex-1 resize-y" placeholder="Share a reminder, a question or a moment of gratitude..." aria-label="Post text">{{ old('body') }}</textarea></div>
                @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror @error('images.*')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <div class="flex flex-wrap items-center gap-2">
                    <label class="btn btn-outline btn-sm cursor-pointer"><x-lucide-image class="size-4" />Photos<input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="sr-only" @change="n = $event.target.files.length"></label>
                    <span x-show="n" x-cloak class="text-xs text-muted" x-text="n + ' selected'"></span>
                    <select name="visibility" class="field !min-h-9 !w-auto !py-1 text-sm" aria-label="Who can see this"><option value="public">Public</option><option value="followers">Followers only</option></select>
                    <x-ui.button type="submit" size="sm" class="ms-auto">Post</x-ui.button>
                </div>
            </form>
        @endif
        @forelse ($posts as $post)
            <x-community.post-card :post="$post" :liked="$liked->contains($post->id)" />
        @empty
            <x-ui.empty icon="messages-square" title="{{ $tab === 'following' ? 'Nothing from people you follow yet' : 'No posts yet' }}" text="{{ $tab === 'following' ? 'Follow people to see their posts here.' : 'Be the first to share something beneficial.' }}"><x-ui.button :href="route('app.community.people')" variant="outline">Find people</x-ui.button></x-ui.empty>
        @endforelse
        {{ $posts->links() }}
    </div>
    <x-community.report-sheet />
</x-layouts.app>
