<x-layouts.app title="Post">
    <x-slot:header><x-ui.page-header title="Post" :subtitle="'by '.$post->author->name" :back="route('app.community.index')" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-4 py-5">
        <x-community.post-card :post="$post" :liked="$liked->contains($post->id)" detail />
        <form method="POST" action="{{ route('app.community.comments.store', $post) }}" class="card flex gap-2 p-3">@csrf
            <input name="body" required maxlength="1000" class="field flex-1" placeholder="Write a comment..." aria-label="Comment"><x-ui.button type="submit">Send</x-ui.button>
        </form>
        @error('body')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        <div class="space-y-3">
            @forelse ($comments as $c)
                <div class="card p-3" x-data="{ reply: false }">
                    @include('app.community._comment', ['c' => $c, 'post' => $post, 'likedComments' => $likedComments])
                    @foreach ($c->replies as $r)<div class="ms-8 mt-3 border-s-2 border-line ps-3">@include('app.community._comment', ['c' => $r, 'post' => $post, 'likedComments' => $likedComments])</div>@endforeach
                    <form x-show="reply" x-cloak method="POST" action="{{ route('app.community.comments.store', $post) }}" class="ms-8 mt-3 flex gap-2">@csrf<input type="hidden" name="parent_id" value="{{ $c->id }}"><input name="body" required maxlength="1000" class="field flex-1" placeholder="Reply..." aria-label="Reply"><x-ui.button type="submit" size="sm">Reply</x-ui.button></form>
                    <button type="button" class="ms-8 mt-2 text-xs font-semibold text-forest-700 dark:text-emerald-500" @click="reply = !reply">Reply</button>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-muted">No comments yet.</p>
            @endforelse
            {{ $comments->links() }}
        </div>
    </div>
    <x-community.report-sheet />
</x-layouts.app>
