@props(['icon', 'label', 'href', 'hint' => null, 'badge' => null])
<a href="{{ $href }}" {{ $attributes->class(['list-row']) }}>
    <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-mint text-forest-700 dark:text-emerald-100"><x-dynamic-component :component="'lucide-'.$icon" class="size-[18px]" /></span>
    <span class="min-w-0 flex-1"><span class="block text-[15px] font-medium">{{ $label }}</span>@if ($hint)<span class="block truncate text-xs text-muted">{{ $hint }}</span>@endif</span>
    @if ($badge)<span class="badge">{{ $badge }}</span>@endif
    <x-lucide-chevron-right class="size-4 shrink-0 text-muted rtl:rotate-180" />
</a>
