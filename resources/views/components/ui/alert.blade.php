@props(['type' => 'info', 'title' => null])
@php
    $map = [
        'info' => ['bg-mint text-forest-800 dark:text-emerald-100', 'info'],
        'success' => ['bg-emerald-100 text-forest-800', 'circle-check'],
        'warning' => ['bg-gold-100 text-[#6b5314]', 'triangle-alert'],
        'danger' => ['bg-red-50 text-red-800', 'circle-alert'],
    ][$type];
@endphp
<div role="{{ $type === 'danger' ? 'alert' : 'status' }}" {{ $attributes->class(['flex gap-3 rounded-xl px-4 py-3 text-sm', $map[0]]) }}>
    <x-dynamic-component :component="'lucide-'.$map[1]" class="mt-0.5 size-5 shrink-0" />
    <div class="min-w-0">@if ($title)<p class="font-semibold">{{ $title }}</p>@endif<div>{{ $slot }}</div></div>
</div>
