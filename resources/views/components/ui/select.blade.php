@props(['label' => null, 'name', 'options' => [], 'value' => null, 'placeholder' => null, 'hint' => null])
@php($id = $attributes->get('id', 'f-'.str_replace(['[', ']', '.'], '-', $name)))
<div {{ $attributes->only('class') }}>
    @if ($label)<label for="{{ $id }}" class="field-label">{{ $label }}</label>@endif
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except(['class', 'id'])->class(['field', 'border-red-400' => $errors->has($name)]) }}>
        @if ($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $k => $v)
            <option value="{{ $k }}" @selected((string) old($name, $value) === (string) $k)>{{ $v }}</option>
        @endforeach
        {{ $slot }}
    </select>
    @if ($hint)<p class="field-hint">{{ $hint }}</p>@endif
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
</div>
