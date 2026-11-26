<x-layouts.app title="{{ $guide->title }}">
    <x-slot:header>
        <x-ui.page-header :title="$guide->title" :subtitle="$guide->summary" :back="route('app.hajj.index')">
            <x-slot:actions><form method="POST" action="{{ route('app.hajj.save', $guide) }}">@csrf<button class="grid size-10 place-items-center rounded-full bg-white/10" aria-label="{{ $isSaved ? 'Remove from saved' : 'Save guide' }}"><x-lucide-bookmark class="size-5" fill="{{ $isSaved ? 'currentColor' : 'none' }}" /></button></form></x-slot:actions>
        </x-ui.page-header>
    </x-slot:header>
    <div class="grid gap-5 py-5 lg:grid-cols-3">
        <div class="space-y-3 lg:col-span-2">
            <h2 class="font-bold">Steps</h2>
            @foreach ($guide->steps as $i => $s)
                <article class="card p-4">
                    <div class="flex items-center gap-3"><span class="grid size-8 shrink-0 place-items-center rounded-full bg-forest-700 text-sm font-bold text-white">{{ $i + 1 }}</span><h3 class="font-semibold">{{ $s->title }}</h3></div>
                    <div class="prose-siraj mt-3">{!! \Illuminate\Support\Str::markdown($s->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                    @if ($s->arabic_text)<p class="quran-text mt-3 rounded-xl bg-mint p-3 text-2xl" dir="rtl" lang="ar">{{ $s->arabic_text }}</p>@endif
                    @if ($s->transliteration)<p class="mt-2 text-sm italic text-muted">{{ $s->transliteration }}</p>@endif
                    @if ($s->translation_en)<p class="mt-2 text-sm">{{ $s->translation_en }}</p>@endif
                    @if ($s->reference)<p class="mt-2 text-xs text-muted">Reference: {{ $s->reference }}</p>@endif
                </article>
            @endforeach
        </div>
        <aside class="space-y-3" x-data="{ done: {{ $done->count() }}, total: {{ $itemCount }}, toggle(e, id) { fetch('/app/hajj-umrah/checklist/' + id, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, Accept: 'application/json' } }).then(r => r.json()).then(d => { this.done += d.checked ? 1 : -1 }).catch(() => { e.target.checked = !e.target.checked; window.toast('Connect to the internet to update your checklist', 'warning') }) } }">
            <div class="flex items-center justify-between"><h2 class="font-bold">Checklist</h2><span class="text-sm text-muted" x-text="done + ' / ' + total"></span></div>
            @forelse ($items as $cat => $list)
                <section class="card p-4"><h3 class="mb-2 text-xs font-semibold uppercase tracking-widest text-muted">{{ $cat }}</h3>
                    @foreach ($list as $item)<label class="flex cursor-pointer items-start gap-3 py-1.5"><input type="checkbox" class="mt-0.5 size-5 rounded-md border-line text-forest-700" @checked(isset($done[$item->id])) @change="toggle($event, {{ $item->id }})"><span class="text-sm">{{ $item->label }}</span></label>@endforeach
                </section>
            @empty
                <p class="text-sm text-muted">No checklist for this guide.</p>
            @endforelse
        </aside>
    </div>
</x-layouts.app>
