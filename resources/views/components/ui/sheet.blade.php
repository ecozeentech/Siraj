{{-- Bottom sheet on mobile, centred dialog on desktop. Open with: $dispatch('open-sheet', 'name') --}}
@props(['name', 'title' => null])
<div x-data="{ open: false }" x-on:open-sheet.window="if ($event.detail === '{{ $name }}') open = true" x-on:close-sheet.window="open = false" x-on:keydown.escape.window="open = false" x-show="open" x-cloak
     class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center" role="dialog" aria-modal="true" @if ($title) aria-label="{{ $title }}" @endif>
    <div class="absolute inset-0 bg-forest-950/50 backdrop-blur-[2px]" x-show="open" x-transition.opacity @click="open = false"></div>
    <div class="relative max-h-[88dvh] w-full overflow-y-auto rounded-t-3xl bg-surface p-5 shadow-pop sm:max-w-md sm:rounded-3xl" x-show="open" x-transition:enter="transition duration-200" x-transition:enter-start="translate-y-full sm:translate-y-4 sm:opacity-0" x-transition:enter-end="translate-y-0 opacity-100" style="padding-bottom: calc(1.25rem + var(--safe-bottom))">
        <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-line sm:hidden"></div>
        @if ($title)<div class="mb-3 flex items-center justify-between"><h3 class="text-lg font-bold">{{ $title }}</h3><button type="button" @click="open = false" class="grid size-8 place-items-center rounded-full bg-surface-2" aria-label="Close"><x-lucide-x class="size-4" /></button></div>@endif
        {{ $slot }}
    </div>
</div>
