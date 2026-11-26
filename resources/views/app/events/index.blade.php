<x-layouts.app title="Events">
    <x-slot:header>
        <x-ui.page-header title="Events" subtitle="Lectures, programmes and gatherings" :back="route('app.more')">
            <x-slot:actions><a href="{{ route('app.events.mine') }}" class="btn btn-sm bg-white/10 text-white hover:bg-white/20"><x-lucide-ticket class="size-4" />My events</a></x-slot:actions>
        </x-ui.page-header>
    </x-slot:header>
    <div class="space-y-4 py-5">
        <form method="GET" class="flex gap-2"><x-ui.input name="q" :value="$q" icon="search" placeholder="Search events or city" class="flex-1" />@if ($category)<input type="hidden" name="category" value="{{ $category }}">@endif<x-ui.button type="submit">Search</x-ui.button></form>
        <nav class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1" aria-label="Event categories">
            <a href="{{ route('app.events.index', ['q' => $q ?: null]) }}" class="chip {{ ! $category ? 'chip-active' : '' }}">All</a>
            @foreach ($categories as $c)<a href="{{ route('app.events.index', ['category' => $c->slug, 'q' => $q ?: null]) }}" class="chip {{ $category === $c->slug ? 'chip-active' : '' }}">{{ $c->name }}</a>@endforeach
            <a href="{{ route('app.events.index', ['free' => 1]) }}" class="chip {{ request()->boolean('free') ? 'chip-active' : '' }}">Free</a>
        </nav>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($events as $e)
                <a href="{{ route('app.events.show', $e) }}" class="card block overflow-hidden transition hover:shadow-md">
                    <div class="relative"><x-art.cover :theme="$e->cover_theme" :src="$e->cover_path ? Storage::disk('public')->url($e->cover_path) : null" :alt="$e->title" class="h-36 w-full" /><span class="absolute start-3 top-3 rounded-xl bg-white px-2.5 py-1 text-center text-xs font-bold leading-tight text-forest-800 shadow"><span class="block text-base">{{ $e->starts_at->format('d') }}</span>{{ $e->starts_at->format('M') }}</span></div>
                    <div class="p-4"><div class="flex items-center gap-2"><span class="badge">{{ $e->category?->name ?? 'Event' }}</span>@if ($e->status === 'cancelled')<span class="badge badge-x">Cancelled</span>@endif @if (isset($mine[$e->id]))<span class="badge badge-check">{{ $mine[$e->id] === 'registered' ? 'Registered' : ucfirst(str_replace('_', ' ', $mine[$e->id])) }}</span>@endif</div>
                        <h2 class="mt-2 font-bold leading-snug">{{ $e->title }}</h2>
                        <p class="mt-1 text-sm text-muted"><x-lucide-clock class="inline size-3.5" /> {{ $e->starts_at->format('D j M, g:ia') }}</p>
                        <p class="text-sm text-muted"><x-lucide-map-pin class="inline size-3.5" /> {{ $e->mode === 'online' ? 'Online' : ($e->city ?: $e->venue_name ?: 'Venue to be announced') }}</p>
                        <p class="mt-2 text-sm font-semibold text-forest-700 dark:text-emerald-500">{{ $e->isFree() ? 'Free' : \App\Support\Money::format($e->price_kobo, $e->currency) }}@if ($e->capacity !== null) <span class="font-normal text-muted">· {{ max(0, $e->capacity - $e->taken_count) }} places left</span>@endif</p></div>
                </a>
            @empty
                <div class="sm:col-span-2 lg:col-span-3"><x-ui.empty icon="calendar-x" title="No upcoming events" text="Check back soon, or try a different filter." /></div>
            @endforelse
        </div>
        {{ $events->links() }}
    </div>
</x-layouts.app>
