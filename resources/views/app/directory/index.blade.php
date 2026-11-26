<x-layouts.app title="Directory">
    <x-slot:header>
        <x-ui.page-header title="Directory" subtitle="Mosques and Muslim-friendly businesses" :back="route('app.more')">
            <x-slot:actions><a href="{{ route('app.directory.submit') }}" class="btn btn-sm bg-white/10 text-white hover:bg-white/20"><x-lucide-plus class="size-4" />Add listing</a></x-slot:actions>
            <nav class="mx-auto mt-4 flex max-w-6xl gap-2 text-sm font-semibold" aria-label="Directory sections">
                @foreach (['mosques' => 'Mosques', 'businesses' => 'Businesses', 'halal' => 'Verified halal'] as $k => $l)<a href="{{ route('app.directory.index', ['tab' => $k]) }}" class="rounded-full px-4 py-1.5 {{ $tab === $k ? 'bg-white text-forest-800' : 'bg-white/10 text-white' }}">{{ $l }}</a>@endforeach
            </nav>
        </x-ui.page-header>
    </x-slot:header>
    <div class="space-y-4 py-5" x-data="{ locating: false, near() { this.locating = true; navigator.geolocation.getCurrentPosition(p => { const u = new URL(location.href); u.searchParams.set('lat', p.coords.latitude.toFixed(5)); u.searchParams.set('lng', p.coords.longitude.toFixed(5)); u.searchParams.delete('page'); location.href = u }, () => { this.locating = false; window.toast('Location unavailable. You can still search by city.', 'error') }, { timeout: 10000 }) } }">
        <form method="GET" class="grid gap-2 sm:grid-cols-[1fr_12rem_auto_auto]"><input type="hidden" name="tab" value="{{ $tab }}">
            <x-ui.input name="q" :value="$q" icon="search" placeholder="Search by name or address" />
            <x-ui.select name="city" :value="$city" placeholder="All cities" :options="$cities->mapWithKeys(fn ($c) => [$c => $c])->all()" />
            <x-ui.button type="submit">Search</x-ui.button>
            <button type="button" class="btn btn-outline" @click="near()" :disabled="locating"><x-lucide-locate-fixed class="size-4" />Near me</button>
        </form>
        @if ($tab !== 'mosques')
            <nav class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1" aria-label="Business categories"><a href="{{ route('app.directory.index', ['tab' => $tab]) }}" class="chip {{ ! $category ? 'chip-active' : '' }}">All</a>@foreach ($categories as $c)<a href="{{ route('app.directory.index', ['tab' => $tab, 'category' => $c->slug]) }}" class="chip {{ $category === $c->slug ? 'chip-active' : '' }}">{{ $c->name }}</a>@endforeach</nav>
        @endif
        @if ($tab === 'halal')<x-ui.alert type="info" title="About halal verification">Only businesses whose halal certificate has been checked by SIRAJ staff appear here. SIRAJ does not certify food or businesses itself; always confirm with the certifier when in doubt.</x-ui.alert>@endif
        @if ($lat !== null)<p class="text-sm text-muted">Sorted by distance from your location. <a class="font-semibold underline" href="{{ route('app.directory.index', ['tab' => $tab]) }}">Clear</a></p>@endif
        <div class="grid gap-3 md:grid-cols-2">
            @forelse ($items as $i)
                @if ($tab === 'mosques')
                    <a href="{{ route('app.directory.mosque', $i) }}" class="card flex gap-3 p-4 transition hover:shadow-md"><span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-mint text-forest-700"><x-lucide-landmark class="size-6" /></span><span class="min-w-0 flex-1"><span class="flex items-center gap-2"><span class="truncate font-bold">{{ $i->name }}</span>@if ($i->verification_status === 'verified')<x-lucide-badge-check class="size-4 shrink-0 text-forest-700" aria-label="Details verified" />@endif</span><span class="block truncate text-sm text-muted">{{ collect([$i->address, $i->city])->filter()->implode(', ') }}</span>@if ($i->jummah_time)<span class="mt-1 block text-xs text-muted">Jumu'ah {{ $i->jummah_time }}</span>@endif @if (isset($i->distance_km))<span class="mt-1 block text-xs font-semibold text-forest-700">{{ number_format($i->distance_km, 1) }} km away</span>@endif</span></a>
                @else
                    <a href="{{ route('app.directory.business', $i) }}" class="card flex gap-3 p-4 transition hover:shadow-md"><span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-gold-100 text-gold-600"><x-dynamic-component :component="'lucide-'.($i->category?->icon ?: 'store')" class="size-6" /></span><span class="min-w-0 flex-1"><span class="block truncate font-bold">{{ $i->name }}</span><span class="block truncate text-sm text-muted">{{ $i->category?->name }} · {{ $i->city }}</span><span class="mt-1 flex flex-wrap items-center gap-2 text-xs">@if ($i->reviews_count)<span class="font-semibold"><x-lucide-star class="inline size-3.5 text-gold-500" fill="#e4b960" /> {{ number_format($i->rating_average, 1) }} ({{ $i->reviews_count }})</span>@endif @if ($i->halal_status === 'verified')<span class="badge badge-check">Halal verified</span>@endif @if (isset($i->distance_km))<span class="font-semibold text-forest-700">{{ number_format($i->distance_km, 1) }} km</span>@endif</span></span></a>
                @endif
            @empty
                <div class="md:col-span-2"><x-ui.empty icon="map-pin-off" title="Nothing found" text="Try a different search or city, or add a listing." /></div>
            @endforelse
        </div>
        {{ $items->links() }}
    </div>
</x-layouts.app>
