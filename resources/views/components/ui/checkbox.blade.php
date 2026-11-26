@props(['name', 'label', 'checked' => false, 'value' => '1', 'hint' => null])
<div {{ $attributes->only('class') }}>
    <label class="flex cursor-pointer items-start gap-3">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" value="{{ $value }}" @checked(old($name, $checked)) class="mt-0.5 size-5 shrink-0 rounded-md border-line text-forest-700 focus:ring-emerald-brand">
        <span class="text-sm text-ink">{{ $label }}@if ($hint)<span class="block text-xs text-muted">{{ $hint }}</span>@endif</span>
    </label>
    @error($name)<p class="field-error">{{ $message }}</p>@enderror
</div>
