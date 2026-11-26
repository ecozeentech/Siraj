<x-layouts.app title="Bookmarks & notes">
    <x-slot:header><x-ui.page-header title="Bookmarks & notes" :back="route('app.quran.index')" /></x-slot:header>
    <div class="space-y-6 py-5">
        <section>
            <h2 class="mb-2 font-bold">Bookmarks</h2>
            <div class="space-y-3">
                @forelse ($items as $b)
                    <a href="{{ route('app.quran.surah', ['surah' => $b->ayah->surah_id, 'ayah' => $b->ayah->number_in_surah]) }}" class="card block p-4">
                        <span class="flex items-center justify-between text-xs font-bold text-forest-700 dark:text-emerald-500"><span>{{ $b->ayah->surah->name_en }} {{ $b->ayah->reference() }}</span><span class="text-muted">{{ $b->created_at->diffForHumans() }}</span></span>
                        <span class="quran-text mt-2 block text-[26px]" dir="rtl" lang="ar">{{ $b->ayah->displayText() }}</span>
                        @if ($tr[$b->ayah_id] ?? null)<span class="mt-2 block text-sm leading-6 text-muted">{{ $tr[$b->ayah_id] }}</span>@endif
                        @if ($notes[$b->ayah_id] ?? null)<span class="mt-2 block rounded-lg bg-gold-100/70 p-2 text-sm text-[#5a4511]">{{ $notes[$b->ayah_id] }}</span>@endif
                    </a>
                @empty
                    <x-ui.empty icon="bookmark" title="No bookmarks yet" text="Tap Bookmark while reading to save an ayah here." />
                @endforelse
            </div>
            {{ $items->links() }}
        </section>
        @if ($allNotes->isNotEmpty())
            <section>
                <h2 class="mb-2 font-bold">My notes</h2>
                <div class="card divide-y divide-line overflow-hidden">
                    @foreach ($allNotes as $n)
                        <div class="flex items-start gap-3 p-4"><a href="{{ route('app.quran.surah', ['surah' => $n->surah_id, 'ayah' => $n->number_in_surah]) }}" class="min-w-0 flex-1"><span class="text-xs font-bold text-forest-700 dark:text-emerald-500">{{ $n->name_en }} {{ $n->surah_id }}:{{ $n->number_in_surah }}</span><span class="mt-1 block text-sm">{{ $n->body }}</span></a>
                            <form method="POST" action="{{ route('app.quran.notes.destroy', $n->id) }}" onsubmit="return confirm('Delete this note?')">@csrf @method('DELETE')<button class="grid size-8 place-items-center rounded-full text-muted hover:bg-surface-2" aria-label="Delete note"><x-lucide-trash-2 class="size-4" /></button></form></div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
