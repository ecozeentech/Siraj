@props(['label' => null, 'name', 'type' => 'text', 'hint' => null, 'value' => null, 'icon' => null])
@php
    $id = $attributes->get('id', 'f-'.str_replace(['[', ']', '.'], '-', $name));
    $isPassword = $type === 'password';
@endphp
<div {{ $attributes->only('class') }} @if ($isPassword) x-data="{ show: false }" @endif>
    @if ($label)<label for="{{ $id }}" class="field-label">{{ $label }}</label>@endif
    <div class="relative">
        @if ($icon)<x-dynamic-component :component="'lucide-'.$icon" class="pointer-events-none absolute start-3.5 top-1/2 size-4 -translate-y-1/2 text-muted" />@endif
        <input id="{{ $id }}" name="{{ $name }}"
            @if ($isPassword) :type="show ? 'text' : 'password'" @else type="{{ $type }}" @endif
            @if ($type !== 'password' && $type !== 'file') value="{{ old($name, $value) }}" @endif
            {{ $attributes->except(['class', 'id'])->class(['field', 'ps-10' => $icon, 'pe-11' => $isPassword, 'border-red-400' => $errors->has($name)]) }}>
        @if ($isPassword)
            <button type="button" @click="show = !show" class="absolute end-2 top-1/2 grid size-8 -translate-y-1/2 place-items-center rounded-full text-muted" aria-label="Show or hide password">
                <x-lucide-eye class="size-4" x-show="!show" /><x-lucide-eye-off class="size-4" x-show="show" x-cloak />
            </button>
        @endif
    </div>
    @if ($hint)<p class="field-hint">{{ $hint }}</p>@endif
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
</div>
