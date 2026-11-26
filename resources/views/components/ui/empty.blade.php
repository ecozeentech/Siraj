@props(['icon' => 'inbox', 'title', 'text' => null])
<div {{ $attributes->class(['flex flex-col items-center px-6 py-12 text-center']) }}>
    <span class="grid size-14 place-items-center rounded-full bg-mint text-forest-700 dark:text-emerald-100"><x-dynamic-component :component="'lucide-'.$icon" class="size-7" /></span>
    <h3 class="mt-4 text-base font-semibold">{{ $title }}</h3>
    @if ($text)<p class="mt-1 max-w-xs text-sm text-muted">{{ $text }}</p>@endif
    @if (trim($slot))<div class="mt-5">{{ $slot }}</div>@endif
</div>
