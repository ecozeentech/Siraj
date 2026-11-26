<div class="flex gap-3">
    <a href="{{ route('app.community.profile', $c->author) }}"><x-ui.avatar :user="$c->author" size="sm" /></a>
    <div class="min-w-0 flex-1">
        <p class="text-sm"><a href="{{ route('app.community.profile', $c->author) }}" class="font-semibold">{{ $c->author->name }}</a> <span class="text-xs text-muted">{{ $c->created_at->diffForHumans() }}</span></p>
        <p class="mt-0.5 whitespace-pre-line break-words">{{ $c->body }}</p>
        <div class="mt-1 flex items-center gap-3 text-xs text-muted" x-data="{ liked: @js($likedComments->contains($c->id)), count: {{ $c->reactions()->count() }}, async like() { const r = await fetch(@js(route('app.community.react', ['comment', $c->id])), { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' } }); if (r.ok) { const d = await r.json(); this.liked = d.liked; this.count = d.count } } }">
            <button type="button" @click="like()" class="inline-flex items-center gap-1 font-semibold" :class="liked ? 'text-red-500' : ''"><x-lucide-heart class="size-3.5" x-bind:fill="liked ? 'currentColor' : 'none'" /><span x-text="count"></span></button>
            @if ($c->user_id === auth()->id() || $post->user_id === auth()->id())
                <form method="POST" action="{{ route('app.community.comments.destroy', $c) }}" onsubmit="return confirm('Delete this comment?')">@csrf @method('DELETE')<button class="hover:text-red-600">Delete</button></form>
            @endif
            @if ($c->user_id !== auth()->id())<button type="button" class="hover:underline" @click="$dispatch('report', { url: @js(route('app.community.report', ['comment', $c->id])), label: 'comment' })">Report</button>@endif
        </div>
    </div>
</div>
