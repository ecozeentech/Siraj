{{-- Kids Deen World shell: bright, large touch targets, no adult navigation. --}}
@props(['title' => null, 'child' => null, 'back' => null])
<x-layouts.base :title="$title ? $title.' · Kids' : 'Kids Deen World'" body-class="bg-[#f4fbf0]" theme-color="#b9e6fd">
<div class="mx-auto min-h-dvh max-w-3xl pb-24" x-data="kidsTimer(@js(route('app.kids.heartbeat')), @js($child?->id))">
    <header class="flex items-center gap-3 px-4 pb-2 pt-4" style="padding-top: calc(1rem + var(--safe-top))">
        @if ($back)<a href="{{ $back }}" class="grid size-11 place-items-center rounded-full bg-white shadow-card" aria-label="Back"><x-lucide-arrow-left class="size-5" /></a>@endif
        <a href="{{ $child ? route('app.kids.home') : route('app.kids.index') }}" class="font-display text-2xl font-bold leading-none"><span class="text-gold-500">Kids</span> <span class="text-sky-600">Deen World</span></a>
        <div class="ms-auto flex items-center gap-2">
            @if ($child)
                <span class="flex items-center gap-2 rounded-full bg-white py-1 pe-3 ps-1 text-sm font-bold shadow-card"><span class="grid size-8 place-items-center rounded-full bg-gold-200 text-base">{{ $child->avatar === 'girl' ? '🧕' : '👦' }}</span>{{ $child->name }}</span>
                <button type="button" @click="$dispatch('open-sheet', 'kids-exit')" class="grid size-11 place-items-center rounded-full bg-white shadow-card" aria-label="Parent area"><x-lucide-lock class="size-5 text-forest-700" /></button>
            @endif
        </div>
    </header>
    <main id="main" class="px-4">{{ $slot }}</main>
    @if ($child)
        <x-ui.sheet name="kids-exit" title="Parent area">
            <p class="text-sm text-muted">Enter your SIRAJ account password to leave Kids mode.</p>
            <form method="POST" action="{{ route('app.kids.exit') }}" class="mt-3 space-y-3">@csrf
                <x-ui.input name="password" type="password" label="Parent password" required autocomplete="current-password" />
                <x-ui.button type="submit" block>Exit Kids mode</x-ui.button>
            </form>
        </x-ui.sheet>
    @endif
</div>
</x-layouts.base>
