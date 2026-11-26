@props(['title' => null, 'headerless' => false, 'wide' => false, 'flush' => false])
<x-layouts.base :title="$title">
@php($nav = \App\Support\Navigation::primary())
<div class="min-h-dvh lg:flex">
    {{-- Desktop sidebar --}}
    <aside class="fixed inset-y-0 start-0 z-40 hidden w-64 flex-col overflow-y-auto bg-forest-950 text-white lg:flex">
        <a href="{{ route('app.home') }}" class="px-5 pb-4 pt-6"><x-brand.logo size="sm" /></a>
        <nav class="flex-1 space-y-5 px-3 pb-6" aria-label="Primary">
            @foreach (\App\Support\Navigation::sidebar() as $group => $items)
                <div>
                    <p class="px-3 pb-1.5 text-[11px] font-semibold uppercase tracking-widest text-white/40">{{ $group }}</p>
                    @foreach ($items as [$label, $route, $icon, $patterns])
                        @php($active = \App\Support\Navigation::isActive($patterns))
                        <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-white/10 text-gold-300' : 'text-white/75 hover:bg-white/5 hover:text-white' }}">
                            <x-dynamic-component :component="'lucide-'.$icon" class="size-[18px]" />{{ $label }}
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
        @if (auth()->user()?->is_staff)
            <a href="{{ route('admin.dashboard') }}" class="m-3 flex items-center gap-2 rounded-xl border border-white/10 px-3 py-2.5 text-sm text-white/70 hover:bg-white/5"><x-lucide-shield-check class="size-4" />Admin portal</a>
        @endif
    </aside>

    <div class="min-w-0 flex-1 lg:ps-64">
        @isset($header)
            {{ $header }}
        @endisset
        <main id="main" class="{{ $flush ? '' : 'mx-auto px-4 lg:px-8 '.($wide ? 'max-w-7xl' : 'max-w-6xl') }} pb-28 lg:pb-12 {{ $headerless || isset($header) ? '' : 'pt-4' }}">
            {{ $slot }}
        </main>
    </div>

    {{-- Mobile bottom navigation (reference: Home · Quran · Worship · Learn · More) --}}
    <nav class="safe-bottom fixed inset-x-0 bottom-0 z-50 border-t border-line bg-surface/95 backdrop-blur lg:hidden" aria-label="Primary">
        <ul class="mx-auto grid max-w-lg grid-cols-5">
            @foreach ($nav as [$label, $route, $icon, $patterns])
                @php($active = \App\Support\Navigation::isActive($patterns))
                <li>
                    <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif class="flex min-h-14 flex-col items-center justify-center gap-0.5 text-[11px] font-semibold {{ $active ? 'text-forest-700 dark:text-emerald-500' : 'text-muted' }}">
                        <x-dynamic-component :component="'lucide-'.$icon" class="size-[22px]" :stroke-width="$active ? 2.4 : 1.8" />{{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</div>
</x-layouts.base>
