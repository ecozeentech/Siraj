<x-layouts.app title="Events" headerless flush>
    <div class="mx-auto max-w-md px-4 pt-3">
        <div class="flex items-center gap-2">
            <a href="{{ url()->previous() }}" data-page-back onclick="if (document.referrer && document.referrer.startsWith(location.origin)) { event.preventDefault(); history.back(); }" class="icon-pop grid size-11 place-items-center rounded-full hover:bg-surface-2" aria-label="{{ __('Back') }}"><x-lucide-chevron-left class="size-5" /></a>
            <h1 class="flex-1 text-2xl font-bold">{{ __('Events') }}</h1>
            <x-ui.bell />
        </div>
        <div class="mt-3 grid grid-cols-3 gap-2 rounded-full bg-surface-2 p-1 text-sm font-semibold" role="tablist">
            @foreach (['upcoming' => __('Upcoming'), 'nearby' => __('Nearby'), 'online' => __('Online')] as $key => $label)
                <a role="tab" aria-selected="{{ $tab === $key ? 'true' : 'false' }}" href="{{ route('app.events.index', ['tab' => $key === 'upcoming' ? null : $key]) }}" class="flex min-h-10 items-center justify-center rounded-full {{ $tab === $key ? 'bg-[#128a49] text-white' : 'text-ink' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="mt-4 space-y-3 pb-4">
            @forelse ($events as $e)
                @php($when = $e->starts_at->timezone($e->timezone ?: config('app.timezone')))
                @if ($loop->first)
                    <a href="{{ route('app.events.show', $e) }}" class="icon-pop relative block h-56 overflow-hidden rounded-[1.4rem] text-white shadow-card">
                        <img src="{{ $e->cover_path ? \Illuminate\Support\Facades\Storage::url($e->cover_path) : asset('images/dash-event-cover.png') }}" alt="" class="absolute inset-0 size-full object-cover">
                        <span class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/15 to-transparent"></span>
                        <span class="absolute inset-x-0 bottom-0 p-4">
                            <span class="block text-xl font-bold leading-tight">{{ $e->title }}</span>
                            <span class="mt-1 block text-sm">{{ $when->format('D, j M') }} · {{ $when->format('g:i A') }}</span>
                            <span class="block text-sm">{{ $e->mode === 'online' ? __('Online') : ($e->venue_name ?: $e->city ?: __('In person')) }}</span>
                            <span class="mt-3 inline-flex min-h-11 items-center gap-1 rounded-full bg-white px-4 text-sm font-bold text-[#128a49]">{{ isset($mine[$e->id]) ? __('View Details') : __('Register Now') }} <x-lucide-arrow-right class="size-4" /></span>
                        </span>
                    </a>
                @else
                    <a href="{{ route('app.events.show', $e) }}" class="icon-pop card flex items-center gap-3 p-3">
                        <x-art.cover :theme="$e->cover_theme" :src="$e->cover_path ? \Illuminate\Support\Facades\Storage::url($e->cover_path) : null" class="size-16 shrink-0 rounded-2xl" />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-bold">{{ $e->title }}</span>
                            <span class="mt-0.5 block text-sm text-ink">{{ $when->format('D, j M') }} · {{ $when->format('g:i A') }}</span>
                            <span class="block text-sm text-ink">{{ $e->mode === 'online' ? __('Online') : ($e->venue_name ?: $e->city) }}</span>
                            <span class="mt-1 inline-flex font-semibold text-[#128a49]">{{ __('View Details') }}</span>
                        </span>
                    </a>
                @endif
            @empty
                <x-ui.empty icon="calendar-x" title="No upcoming events" text="Check back soon, or try another tab." />
            @endforelse
            {{ $events->links() }}
        </div>
    </div>
</x-layouts.app>
