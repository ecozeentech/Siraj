<x-layouts.app title="Complete payment">
    <x-slot:header><x-ui.page-header title="Complete your payment" subtitle="{{ $provider?->name ?: 'Payment' }}" /></x-slot:header>
    <div class="mx-auto max-w-xl space-y-4 py-5">
        <section class="card space-y-2 p-5">
            <p class="text-sm font-semibold text-ink">Amount</p>
            <p class="text-3xl font-bold">{{ \App\Support\Money::format($tx->amount_kobo, $tx->currency) }}</p>
            <p class="text-sm text-ink">Reference <span class="font-bold">{{ $tx->reference }}</span></p>
            <p class="text-sm text-ink">Status: {{ ucfirst($tx->status) }}</p>
        </section>
        <section class="card space-y-2 p-5">
            <p class="leading-7 text-ink">{{ $provider?->instructions }}</p>
            @foreach ($provider?->public_details ?? [] as $label => $value)
                @if (filled($value))<p class="text-ink"><span class="font-semibold">{{ str_replace('_', ' ', ucfirst($label)) }}:</span> {{ $value }}</p>@endif
            @endforeach
        </section>
        <x-ui.alert type="info">This payment stays pending until a SIRAJ administrator confirms it. Nothing is added to your wallet before then.</x-ui.alert>
        <x-ui.button :href="route('app.wallet.index')" variant="outline">Back to wallet</x-ui.button>
    </div>
</x-layouts.app>
