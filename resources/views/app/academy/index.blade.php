<x-layouts.app title="Academy">
    <x-slot:header>
        <header class="hero-green pattern-geo px-4 pb-6 lg:rounded-b-[2rem] lg:px-8" style="padding-top: calc(1rem + var(--safe-top))">
            <div class="mx-auto max-w-6xl">
                <div class="flex items-center gap-3"><div class="min-w-0 flex-1"><h1 class="text-xl font-bold">Islamic Academy</h1><p class="text-sm text-white/75">Learn. Grow. Apply.</p></div><x-ui.bell /></div>
                @if ($featured)
                    <div class="relative mt-4 overflow-hidden rounded-2xl border border-white/15 bg-forest-950/40 p-5">
                        <x-lucide-book-open class="pointer-events-none absolute -bottom-3 end-3 size-36 text-gold-300/25" stroke-width="1" />
                        <h2 class="text-lg font-bold">{{ $featured->title }}</h2><p class="mt-1 max-w-xs text-sm text-white/80">{{ $featured->summary }}</p>
                        <a href="{{ route('app.academy.course', $featured) }}" class="btn btn-gold btn-sm mt-4">Start Learning <x-lucide-arrow-right class="size-4" /></a>
                    </div>
                @endif
            </div>
        </header>
    </x-slot:header>
    <div class="space-y-4 py-5">
        <div class="flex gap-2 overflow-x-auto" role="tablist">
            @foreach (['all' => 'All Courses', 'progress' => 'In Progress', 'completed' => 'Completed'] as $k => $label)<a role="tab" aria-selected="{{ $filter === $k ? 'true' : 'false' }}" href="{{ route('app.academy.index', $k === 'all' ? [] : ['filter' => $k]) }}" class="chip shrink-0 {{ $filter === $k ? 'chip-active' : '' }}">{{ $label }}</a>@endforeach
        </div>
        <div class="grid gap-3 md:grid-cols-2">
            @forelse ($courses as $c)
                <a href="{{ route('app.academy.course', $c) }}" class="card flex gap-3 p-3">
                    <x-art.cover :theme="$c->cover_theme" :src="$c->cover_path ? \Illuminate\Support\Facades\Storage::url($c->cover_path) : null" class="size-20 shrink-0 rounded-xl" />
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center gap-2"><span class="truncate font-semibold">{{ $c->title }}</span>@if ($c->is_premium)<span class="badge badge-gold">Plus</span>@endif</span>
                        <span class="block text-sm text-muted">{{ ucfirst($c->level) }} Level · {{ $c->lessons_count }} lessons</span>
                        @if ($c->state !== 'new')<span class="mt-2 flex items-center gap-2"><x-ui.progress class="flex-1" :value="$c->progress" /><span class="text-xs font-bold">{{ $c->progress }}%</span></span>@else<span class="mt-2 block text-xs font-semibold text-forest-700 dark:text-emerald-500">{{ $c->category?->name }}</span>@endif
                    </span>
                </a>
            @empty
                <div class="md:col-span-2"><x-ui.empty icon="graduation-cap" title="Nothing here yet" text="Courses you start will show up under In Progress."><x-ui.button :href="route('app.academy.index')" size="sm">Browse all courses</x-ui.button></x-ui.empty></div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
