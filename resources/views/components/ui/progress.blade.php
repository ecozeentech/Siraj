@props(['value' => 0, 'tone' => 'green', 'label' => null])
@php($v = max(0, min(100, (int) $value)))
<div {{ $attributes->class(['h-1.5 w-full overflow-hidden rounded-full bg-surface-2']) }} role="progressbar" aria-valuenow="{{ $v }}" aria-valuemin="0" aria-valuemax="100" @if ($label) aria-label="{{ $label }}" @endif>
    <div class="h-full rounded-full {{ $tone === 'gold' ? 'bg-gold-400' : 'bg-forest-700 dark:bg-emerald-500' }}" style="width: {{ $v }}%"></div>
</div>
