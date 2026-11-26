@props(['post', 'liked' => false, 'detail' => false])
@php($me = auth()->user())
<article {{ $attributes->class(['card p-4']) }} x-data="{ liked: @js((bool) $liked), count: {{ $post->reactions_count }}, busy: false,
    async like() { if (this.busy) return; this.busy = true; try { const r = await fetch(@js(route('app.community.react', ['post', $post->id])), { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }); if (r.ok) { const d = await r.json(); this.liked = d.liked; this.count = d.count } else { window.toast('Could not update. Try again.', 'error') } } catch (e) { window.toast('You appear to be offline.', 'error') } this.busy = false } }">
    <header class="flex items-center gap-3">
        <a href="{{ route('app.community.profile', $post->author) }}"><x-ui.avatar :user="$post->author" /></a>
        <div class="min-w-0 flex-1">
            <a href="{{ route('app.community.profile', $post->author) }}" class="block truncate font-semibold hover:underline">{{ $post->author->name }}</a>
            <p class="text-xs text-muted"><a href="{{ route('app.community.posts.show', $post) }}">{{ $post->created_at->diffForHumans() }}</a>@if ($post->group) · in <a class="underline" href="{{ route('app.community.groups.show', $post->group) }}">{{ $post->group->name }}</a>@endif @if ($post->visibility === 'followers') · Followers @endif</p>
        </div>
        <details class="relative">
            <summary class="grid size-9 cursor-pointer list-none place-items-center rounded-full hover:bg-surface-2" aria-label="More options"><x-lucide-ellipsis class="size-5" /></summary>
            <div class="absolute end-0 z-10 mt-1 w-48 rounded-xl border border-line bg-surface p-1 text-sm shadow-pop">
                @if ($post->user_id === $me->id)
                    <form method="POST" action="{{ route('app.community.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">@csrf @method('DELETE')<button class="w-full rounded-lg px-3 py-2 text-start text-red-600 hover:bg-red-50">Delete post</button></form>
                @else
                    <button type="button" class="w-full rounded-lg px-3 py-2 text-start hover:bg-surface-2" @click="$dispatch('report', { url: @js(route('app.community.report', ['post', $post->id])), label: 'post' }); $el.closest('details').open = false">Report post</button>
                    <form method="POST" action="{{ route('app.community.block.store', $post->author) }}" onsubmit="return confirm('Block {{ addslashes($post->author->name) }}? You will no longer see each other.')">@csrf<button class="w-full rounded-lg px-3 py-2 text-start hover:bg-surface-2">Block user</button></form>
                @endif
            </div>
        </details>
    </header>
    @if ($post->body)<p class="mt-3 whitespace-pre-line break-words leading-7">{{ $post->body }}</p>@endif
    @if ($post->media->isNotEmpty())
        <div class="mt-3 grid gap-1.5 overflow-hidden rounded-2xl {{ $post->media->count() > 1 ? 'grid-cols-2' : '' }}">
            @foreach ($post->media as $m)<img src="{{ Storage::disk('public')->url($m->path) }}" alt="{{ $m->alt ?: 'Photo shared by '.$post->author->name }}" loading="lazy" class="max-h-96 w-full object-cover">@endforeach
        </div>
    @endif
    @if ($post->sharedPost && $post->sharedPost->status === 'published')
        <a href="{{ route('app.community.posts.show', $post->sharedPost) }}" class="mt-3 block rounded-2xl border border-line bg-surface-2 p-3"><p class="text-sm font-semibold">{{ $post->sharedPost->author->name }}</p><p class="mt-1 line-clamp-4 whitespace-pre-line text-sm">{{ $post->sharedPost->body }}</p></a>
    @elseif ($post->shared_post_id)
        <p class="mt-3 rounded-2xl border border-dashed border-line p-3 text-sm text-muted">The original post is no longer available.</p>
    @endif
    <footer class="mt-3 flex items-center gap-1 border-t border-line pt-2 text-sm text-muted">
        <button type="button" @click="like()" :aria-pressed="liked" class="flex min-h-10 items-center gap-1.5 rounded-full px-3 hover:bg-surface-2" :class="liked ? 'text-red-500' : ''"><x-lucide-heart class="size-[18px]" x-bind:fill="liked ? 'currentColor' : 'none'" /><span x-text="count"></span></button>
        <a href="{{ route('app.community.posts.show', $post) }}" class="flex min-h-10 items-center gap-1.5 rounded-full px-3 hover:bg-surface-2"><x-lucide-message-circle class="size-[18px]" />{{ $post->comments_count }}</a>
        @if ($post->visibility === 'public' && ! $post->group_id)
            <form method="POST" action="{{ route('app.community.posts.share', $post) }}" onsubmit="return confirm('Share this post to your feed?')">@csrf<button class="flex min-h-10 items-center gap-1.5 rounded-full px-3 hover:bg-surface-2" aria-label="Share to my feed"><x-lucide-repeat-2 class="size-[18px]" />{{ $post->shares_count }}</button></form>
        @endif
    </footer>
</article>
