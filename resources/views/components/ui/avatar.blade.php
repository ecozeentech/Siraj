@props(['user', 'size' => 'md'])
@php
    $px = ['xs' => 'size-7 text-[10px]', 'sm' => 'size-9 text-xs', 'md' => 'size-11 text-sm', 'lg' => 'size-16 text-xl', 'xl' => 'size-24 text-3xl'][$size] ?? 'size-11 text-sm';
    $url = $user?->avatarUrl();
@endphp
<span {{ $attributes->class(["relative inline-grid shrink-0 place-items-center overflow-hidden rounded-full bg-mint font-bold text-forest-800 ring-2 ring-white/80 dark:text-emerald-100", $px]) }}>
    <span aria-hidden="true">{{ $user?->initials() ?: '?' }}</span>
    @if ($url)<img src="{{ $url }}" alt="" loading="lazy" decoding="async" class="absolute inset-0 size-full object-cover" onerror="this.remove()">@endif
</span>
