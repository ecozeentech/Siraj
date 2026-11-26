{{-- Quick-tool tile from the reference home screen: tinted round icon + label on a white card. --}}
@props(['icon', 'label', 'href' => '#', 'tone' => 'green'])
@php
    $tones = ['green' => 'bg-emerald-100 text-forest-700', 'gold' => 'bg-gold-100 text-gold-600', 'pink' => 'bg-pink-100 text-pink-600', 'teal' => 'bg-teal-100 text-teal-700', 'blue' => 'bg-sky-100 text-sky-700', 'purple' => 'bg-violet-100 text-violet-700'];
@endphp
<a href="{{ $href }}" {{ $attributes->class(['card flex flex-col items-center gap-2 px-2 py-3.5 text-center transition active:scale-[0.97] hover:shadow-md']) }}>
    <span class="grid size-11 place-items-center rounded-full {{ $tones[$tone] ?? $tones['green'] }}"><x-dynamic-component :component="'lucide-'.$icon" class="size-5" /></span>
    <span class="text-xs font-semibold leading-tight">{{ $label }}</span>
</a>
