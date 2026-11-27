<x-layouts.app title="Plans">
    <x-slot:header><x-ui.page-header title="SIRAJ Plans" subtitle="Choose what fits your journey" :back="route('app.wallet.index')" /></x-slot:header>
    <div class="space-y-5 py-5">
        @if ($current)<x-ui.alert type="success" title="Current plan: {{ $current->plan->name }}">@if ($current->ends_at) {{ $current->cancelled_at ? 'Ends' : 'Renews or ends' }} {{ $current->ends_at->format('j M Y') }}. @endif
            @if ($current->plan->price_kobo > 0 && ! $current->cancelled_at)<x-ui.confirm class="mt-2" :action="route('app.subscription.cancel')" method="DELETE" message="Stop renewing this plan?" label="Cancel plan" variant="outline" />@endif</x-ui.alert>@endif
        @error('method')<x-ui.alert type="danger">{{ $message }}</x-ui.alert>@enderror
        @unless ($gatewayReady)<x-ui.alert type="warning" title="Online payments are not set up yet">You can still subscribe from your wallet balance. Prices shown are set by SIRAJ administrators.</x-ui.alert>@endunless
        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($plans as $p)
                @php($isCurrent = ($current?->subscription_plan_id ?? $plans->firstWhere('price_kobo', 0)?->id) === $p->id)
                <section class="card flex flex-col p-5 {{ $p->is_featured ? 'ring-2 ring-gold-400' : '' }}">
                    <div class="flex items-center justify-between"><h2 class="text-lg font-bold">{{ $p->name }}</h2>@if ($p->is_featured)<span class="badge badge-gold">Popular</span>@endif</div>
                    <p class="mt-1 text-sm text-muted">{{ $p->description }}</p>
                    <p class="mt-3 text-3xl font-bold">{{ $p->price_kobo ? $p->priceFormatted() : 'Free' }}@if ($p->price_kobo)<span class="text-sm font-semibold text-ink">/{{ $p->interval }}</span>@endif</p>
                    @if ($p->price_kobo && ($local = \App\Support\Money::local($p->price_kobo)))<p class="text-sm font-semibold text-ink">About {{ $local }}</p>@endif
                    <ul class="mt-4 flex-1 space-y-2 text-sm">@foreach ($p->features ?? [] as $f)<li class="flex gap-2"><x-lucide-check class="mt-0.5 size-4 shrink-0 text-forest-700" />{{ $f }}</li>@endforeach</ul>
                    @if ($isCurrent)<span class="btn btn-soft mt-5 pointer-events-none">Current plan</span>
                    @elseif ($p->price_kobo == 0)<form method="POST" action="{{ route('app.subscription.subscribe', $p) }}" class="mt-5">@csrf<x-ui.button type="submit" variant="outline" block>Switch to Free</x-ui.button></form>
                    @else
                        <form method="POST" action="{{ route('app.subscription.subscribe', $p) }}" class="mt-5 space-y-2" x-data="{ m: '{{ $balance >= $p->price_kobo ? 'wallet' : 'gateway' }}' }">@csrf
                            <select name="method" x-model="m" class="field" aria-label="Payment method"><option value="wallet">Wallet ({{ \App\Support\Money::format($balance) }})</option><option value="gateway" @disabled(! $gatewayReady)>Pay online{{ $gatewayReady ? '' : ' (not configured)' }}</option></select>
                            <select name="gateway" x-show="m === 'gateway'" class="field" aria-label="Payment gateway">@foreach ($providers as $provider)<option value="{{ $provider->slug }}">{{ $provider->name }}</option>@endforeach</select>
                            <x-ui.button type="submit" :variant="$p->is_featured ? 'gold' : 'primary'" block>Subscribe</x-ui.button></form>
                    @endif
                </section>
            @endforeach
        </div>
        <p class="text-center text-xs text-muted">Plans do not renew automatically. You will be reminded before your plan ends.</p>
    </div>
</x-layouts.app>
