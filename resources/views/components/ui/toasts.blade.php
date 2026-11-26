<div x-data="toastHost()" x-on:toast.window="push($event.detail)" class="pointer-events-none fixed inset-x-0 top-0 z-[90] flex flex-col items-center gap-2 p-3" style="padding-top: calc(0.75rem + var(--safe-top))" aria-live="polite">
    <template x-for="t in items" :key="t.id">
        <div x-transition class="pointer-events-auto flex max-w-sm items-center gap-2 rounded-2xl px-4 py-3 text-sm font-medium shadow-pop"
             :class="t.type === 'error' ? 'bg-red-600 text-white' : (t.type === 'warning' ? 'bg-gold-200 text-forest-950' : 'bg-forest-900 text-white')">
            <span x-text="t.message"></span>
        </div>
    </template>
</div>
@php($flash = array_filter(['success' => session('status'), 'error' => session('error'), 'warning' => session('warning')]))
@if ($flash)
    <script>document.addEventListener('DOMContentLoaded', () => { @foreach ($flash as $type => $message) window.toast(@js($message), '{{ $type === 'success' ? 'success' : $type }}'); @endforeach });</script>
@endif
