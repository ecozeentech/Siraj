{{-- Preset illustrated covers (no stock imagery). Used for courses, events and kids content that have no uploaded cover. --}}
@props(['theme' => 'mosque_green', 'src' => null, 'alt' => ''])
@php
    $themes = [
        'mosque_green' => ['bg' => 'linear-gradient(180deg,#a9d9b8 0%,#e4f3dc 100%)', 'sky' => 'text-[#1d7a49]', 'lights' => false],
        'mosque_blue' => ['bg' => 'linear-gradient(180deg,#8fb9e6 0%,#f3dcc4 100%)', 'sky' => 'text-[#2b4a6f]', 'lights' => false],
        'mosque_purple' => ['bg' => 'linear-gradient(180deg,#98a2e8 0%,#efd0dc 100%)', 'sky' => 'text-[#473d86]', 'lights' => false],
        'lanterns' => ['bg' => 'linear-gradient(180deg,#171230 0%,#4a2f3a 55%,#d98a3a 100%)', 'sky' => 'text-[#120f0d]', 'lights' => true],
        'dawn' => ['bg' => 'linear-gradient(180deg,#f9cf94 0%,#ee8658 100%)', 'sky' => 'text-[#3a2a2a]', 'lights' => true],
        'quran_open' => ['bg' => 'linear-gradient(160deg,#0d6a43 0%,#003820 100%)', 'sky' => 'text-[#002a18]', 'lights' => false],
    ];
    $t = $themes[$theme] ?? $themes['mosque_green'];
@endphp
<div {{ $attributes->merge(['class' => 'relative isolate overflow-hidden']) }} style="background: {{ $t['bg'] }}">
    @if ($src)
        <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" class="absolute inset-0 size-full object-cover">
    @else
        @if ($theme === 'lanterns' || $theme === 'dawn')
            <span class="absolute right-[18%] top-[16%] size-3 rounded-full bg-gold-200/90 shadow-[0_0_18px_6px_rgba(246,227,174,.45)]"></span>
        @endif
        @if ($theme === 'quran_open')
            <x-lucide-book-open class="absolute left-1/2 top-[38%] size-1/3 -translate-x-1/2 -translate-y-1/2 text-gold-300/90" stroke-width="1.2" />
        @endif
        <x-art.skyline :lights="$t['lights']" class="absolute inset-x-0 bottom-0 h-[55%] {{ $t['sky'] }}" />
    @endif
</div>
