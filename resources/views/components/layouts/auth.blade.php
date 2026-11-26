{{-- Splash/auth shell reproducing reference screen 1: deep green, geometric pattern, skyline, gold CTA. --}}
@props(['title' => null, 'back' => null])
<x-layouts.base :title="$title" body-class="bg-forest-950" theme-color="#00361e">
<div class="hero-green pattern-geo relative isolate flex min-h-dvh flex-col items-center overflow-hidden px-5" style="padding-top: calc(1.5rem + var(--safe-top)); padding-bottom: calc(1.5rem + var(--safe-bottom))">
    <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-1/3 bg-gradient-to-t from-forest-950 to-transparent"></div>
    <x-art.skyline class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-40 text-forest-950/80 sm:h-52" />
    <div class="flex w-full max-w-md flex-1 flex-col">
        <div class="flex h-10 items-center">
            @if ($back)<a href="{{ $back }}" class="grid size-9 place-items-center rounded-full bg-white/10 text-white" aria-label="Back"><x-lucide-chevron-left class="size-5" /></a>@endif
        </div>
        <main id="main" class="flex flex-1 flex-col justify-center py-6">
            <a href="{{ route('home') }}" class="mx-auto mb-6 block" aria-label="SIRAJ Muslim home"><x-brand.logo variant="stacked" size="md" /></a>
            <div class="rounded-3xl bg-surface p-6 text-ink shadow-pop">
                {{ $slot }}
            </div>
        </main>
        <p class="relative z-10 pb-2 text-center text-xs text-white/60">© {{ date('Y') }} SIRAJ Muslim · <a href="{{ route('terms') }}" class="underline-offset-2 hover:underline">Terms</a> · <a href="{{ route('privacy') }}" class="underline-offset-2 hover:underline">Privacy</a></p>
    </div>
</div>
</x-layouts.base>
