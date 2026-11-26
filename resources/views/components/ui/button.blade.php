@props(['variant' => 'primary', 'size' => null, 'href' => null, 'type' => 'button', 'icon' => null, 'block' => false])
@php
    $class = 'btn btn-'.$variant.($size ? ' btn-'.$size : '').($block ? ' btn-block' : '');
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$class]) }}>@if ($icon)<x-dynamic-component :component="'lucide-'.$icon" class="size-4" />@endif{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class([$class]) }}>@if ($icon)<x-dynamic-component :component="'lucide-'.$icon" class="size-4" />@endif{{ $slot }}</button>
@endif
