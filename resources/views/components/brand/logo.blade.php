@props(['variant' => 'horizontal', 'size' => 'md', 'tagline' => false])
@php
    $sizes = [
        'sm' => ['mark' => 'h-9', 'word' => 'text-xl', 'sub' => 'text-[8px]'],
        'md' => ['mark' => 'h-12', 'word' => 'text-3xl', 'sub' => 'text-[10px]'],
        'lg' => ['mark' => 'h-20', 'word' => 'text-5xl', 'sub' => 'text-sm'],
        'xl' => ['mark' => 'h-28', 'word' => 'text-6xl', 'sub' => 'text-base'],
    ][$size];
@endphp
@if ($variant === 'stacked')
    <div {{ $attributes->class(['flex flex-col items-center text-center']) }}>
        <x-brand.mark class="{{ $sizes['mark'] }} w-auto" />
        <span class="mt-3 font-display font-bold leading-none tracking-wide text-white {{ $sizes['word'] }}">SIRAJ</span>
        <span class="mt-2 flex items-center gap-2 font-semibold uppercase tracking-[0.35em] text-gold-400 {{ $sizes['sub'] }}"><i class="h-px w-5 bg-gold-400/70"></i>Muslim<i class="h-px w-5 bg-gold-400/70"></i></span>
        @if ($tagline)<span class="mt-4 max-w-[16rem] font-tagline text-lg italic leading-snug text-white/90">Your Faith. Your Journey. Your Ummah.</span>@endif
    </div>
@else
    <div {{ $attributes->class(['flex items-center gap-3']) }}>
        <x-brand.mark class="{{ $sizes['mark'] }} w-auto" />
        <span class="h-10 w-px bg-gold-400/50"></span>
        <div class="leading-none">
            <div class="font-display font-bold tracking-wide text-white {{ $sizes['word'] }}">SIRAJ</div>
            <div class="mt-1 flex items-center gap-1.5 font-semibold uppercase tracking-[0.3em] text-gold-400 {{ $sizes['sub'] }}"><i class="h-px w-3 bg-gold-400/70"></i>Muslim<i class="h-px w-3 bg-gold-400/70"></i></div>
        </div>
    </div>
@endif
