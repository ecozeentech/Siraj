{{-- Logo for light backgrounds: forest wordmark --}}
@props(['size' => 'md'])
<div {{ $attributes->class(['flex items-center gap-2.5']) }}>
    <x-brand.mark class="{{ $size === 'sm' ? 'h-8' : 'h-10' }} w-auto" />
    <div class="leading-none">
        <div class="font-display font-bold tracking-wide text-forest-800 dark:text-white {{ $size === 'sm' ? 'text-lg' : 'text-2xl' }}">SIRAJ</div>
        <div class="mt-0.5 text-[8px] font-semibold uppercase tracking-[0.3em] text-gold-500">Muslim</div>
    </div>
</div>
