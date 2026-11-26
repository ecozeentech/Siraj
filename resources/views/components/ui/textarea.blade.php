@props(['label' => null, 'name', 'value' => null, 'hint' => null, 'rows' => 4])
@php($id = $attributes->get('id', 'f-'.str_replace(['[', ']', '.'], '-', $name)))
<div {{ $attributes->only('class') }}>
    @if ($label)<label for="{{ $id }}" class="field-label">{{ $label }}</label>@endif
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->except(['class', 'id'])->class(['field', 'border-red-400' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
    @if ($hint)<p class="field-hint">{{ $hint }}</p>@endif
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
</div>
