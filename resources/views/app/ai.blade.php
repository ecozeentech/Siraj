<x-layouts.app title="SIRAJ AI" headerless wide flush>
    <div class="mx-auto flex min-h-dvh max-w-5xl flex-col lg:min-h-[calc(100dvh)]">
        <header x-data class="hero-green pattern-geo px-4 pb-5 text-white lg:rounded-b-[2rem]" style="padding-top: calc(1rem + var(--safe-top))">
            <div class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-full bg-white/15"><x-lucide-sparkles class="size-5 text-gold-300" /></span>
                <div class="min-w-0 flex-1"><h1 class="text-xl font-bold leading-tight">SIRAJ AI</h1><p class="text-sm text-white/75">Your Islamic Knowledge Companion</p></div>
                <button type="button" class="grid size-10 place-items-center rounded-full bg-white/10 hover:bg-white/20" @click="$dispatch('open-sheet', 'ai-history')" aria-label="Conversation history"><x-lucide-history class="size-5" /></button>
                <a href="{{ route('app.ai.index') }}" class="grid size-10 place-items-center rounded-full bg-white/10 hover:bg-white/20" aria-label="New conversation"><x-lucide-square-pen class="size-5" /></a>
            </div>
        </header>
        <livewire:ai.chat :conversation-id="$conversation?->id" />
    </div>
    <x-ui.sheet name="ai-history" title="Conversations">
        @forelse ($history as $h)
            <div class="flex items-center gap-2 border-b border-line py-2 last:border-0">
                <a href="{{ route('app.ai.show', $h) }}" class="min-w-0 flex-1"><p class="truncate font-medium">{{ $h->title }}</p><p class="text-xs text-muted">{{ $h->updated_at->diffForHumans() }}</p></a>
                <x-ui.confirm :action="route('app.ai.destroy', $h)" method="DELETE" message="Delete this conversation?" label="Delete" variant="ghost" />
            </div>
        @empty
            <p class="py-6 text-center text-sm text-muted">No conversations yet.</p>
        @endforelse
    </x-ui.sheet>
</x-layouts.app>
