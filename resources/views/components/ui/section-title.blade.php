@props(['title', 'href' => null, 'link' => 'View All'])
<div {{ $attributes->class(['flex items-center justify-between']) }}>
    <h2 class="text-base font-bold">{{ $title }}</h2>
    @if ($href)<a href="{{ $href }}" class="inline-flex items-center gap-1 text-sm font-semibold text-forest-700 dark:text-emerald-500">{{ $link }}<x-lucide-arrow-right class="size-4" /></a>@endif
</div>
