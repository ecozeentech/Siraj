<x-layouts.app title="Community" headerless flush>
    <div class="mx-auto max-w-md px-4 pt-3">
        <div class="flex items-center gap-2">
            <a href="{{ url()->previous() }}" data-page-back onclick="if (document.referrer && document.referrer.startsWith(location.origin)) { event.preventDefault(); history.back(); }" class="icon-pop grid size-11 place-items-center rounded-full" aria-label="{{ __('Back') }}"><x-lucide-chevron-left class="size-5" /></a>
            <h1 class="flex-1 text-xl font-bold">Muslim Community</h1>
            <x-ui.bell />
        </div>
        <nav class="mt-2 flex gap-5 border-b border-line text-sm font-semibold" aria-label="Community sections">
            @foreach (['latest' => 'For You', 'following' => 'Following'] as $k => $l)
                <a href="{{ route('app.community.index', ['tab' => $k]) }}" class="min-h-11 border-b-2 px-1 {{ $tab === $k ? 'border-[#128a49] text-[#128a49]' : 'border-transparent text-ink' }}">{{ $l }}</a>
            @endforeach
            <a href="{{ route('app.community.groups.index') }}" class="min-h-11 border-b-2 border-transparent px-1 text-ink">Groups</a>
        </nav>
    </div>
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
        <a href="{{ route('app.community.people') }}" class="icon-pop fixed end-5 z-40 grid size-14 place-items-center rounded-full bg-[#128a49] text-white shadow-pop" style="bottom: calc(var(--safe-bottom) + 6.2rem)" aria-label="Find people"><x-lucide-plus class="size-6" /></a>
    </div>
    <x-community.report-sheet />
</x-layouts.app>
