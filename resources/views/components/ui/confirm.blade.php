{{-- Destructive/important action as a POST form guarded by a native confirm dialog. --}}
@props(['action', 'method' => 'POST', 'message' => 'Are you sure?', 'label', 'variant' => 'danger', 'size' => 'sm'])
<form action="{{ $action }}" method="POST" onsubmit="return confirm(@js($message))" {{ $attributes->only('class') }}>
    @csrf
    @if (! in_array(strtoupper($method), ['GET', 'POST']))@method($method)@endif
    <x-ui.button type="submit" :variant="$variant" :size="$size">{{ $label }}</x-ui.button>
</form>
