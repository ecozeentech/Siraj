{{-- Green hero header used by Academy, Profile, AI, etc. (reference screens 4, 7, 9). --}}
@props(['title', 'subtitle' => null, 'back' => null, 'tone' => 'green'])
<header {{ $attributes->class(['relative overflow-hidden px-4 pb-6 pt-4 lg:rounded-b-[2rem] lg:px-8', $tone === 'green' ? 'hero-green pattern-geo' : 'hero-night']) }} style="padding-top: calc(1rem + var(--safe-top))">
    <div class="mx-auto flex max-w-6xl items-center gap-3">
        @if ($back)<a href="{{ $back }}" class="grid size-9 shrink-0 place-items-center rounded-full bg-white/10 hover:bg-white/20 lg:hidden" aria-label="Back"><x-lucide-chevron-left class="size-5" /></a>@endif
        <div class="min-w-0 flex-1">
            <h1 class="truncate text-xl font-bold leading-tight">{{ $title }}</h1>
            @if ($subtitle)<p class="truncate text-sm text-white/75">{{ $subtitle }}</p>@endif
        </div>
        {{ $actions ?? '' }}
    </div>
    {{ $slot }}
</header>
