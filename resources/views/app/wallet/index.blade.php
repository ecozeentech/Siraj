<x-layouts.app title="Wallet">
    <x-slot:header>
        <x-ui.page-header title="Wallet & plan" subtitle="Balance, payments and receipts" :back="route('app.more')">
            <div class="mx-auto mt-4 max-w-6xl rounded-3xl bg-white/10 p-5">
                <p class="text-sm text-white/70">Wallet balance</p>
                <p class="text-4xl font-bold tracking-tight">{{ \App\Support\Money::format($wallet->balance_kobo, $wallet->currency) }}</p>
                @if ($local = \App\Support\Money::local($wallet->balance_kobo))<p class="text-sm text-white">About {{ $local }}, from today’s mid-market rate. Charges stay in naira.</p>@endif
                <p class="mt-2 text-sm text-white/70">Plan: <strong class="text-gold-300">{{ $subscription?->plan?->name ?? 'Free' }}</strong> · <a href="{{ route('app.subscription.index') }}" class="underline">Manage</a></p>
            </div>
        </x-ui.page-header>
    </x-slot:header>
    <div class="grid gap-5 py-5 lg:grid-cols-[22rem_1fr]">
        <div class="space-y-4">
            <section class="card space-y-3 p-5"><h2 class="font-bold">Top up wallet</h2>
                @if (! $gatewayReady)
                    <x-ui.alert type="warning" title="Online payments are not set up yet">Wallet top-ups need a payment provider. An administrator can connect one in Admin &gt; Settings &gt; Payments. Your existing balance can still be used.</x-ui.alert>
                @else
                    @if ($mode === 'sandbox')<x-ui.alert type="info">Payments are in test mode. No real money is charged.</x-ui.alert>@endif
                    <form method="POST" action="{{ route('app.wallet.topup') }}" class="space-y-3" x-data="{ a: '' }">@csrf
                        <div class="flex gap-2">@foreach ([1000, 2000, 5000, 10000] as $p)<button type="button" class="chip flex-1 justify-center" @click="a = {{ $p }}">₦{{ number_format($p) }}</button>@endforeach</div>
                        <x-ui.input name="amount" type="number" min="100" max="1000000" step="1" label="Amount (NGN)" required x-model="a" hint="Minimum ₦100" />
                        <label class="block text-sm font-semibold text-ink">Payment method
                            <select name="gateway" class="field mt-1">@foreach ($providers as $provider)<option value="{{ $provider->slug }}">{{ $provider->name }}</option>@endforeach</select>
                        </label>
                        <x-ui.button type="submit" block icon="wallet">Continue to payment</x-ui.button>
                    </form>
                @endif
            </section>
        </div>
        <div class="space-y-5">
            <section><h2 class="mb-2 font-bold">Wallet activity</h2>
                <div class="card divide-y divide-line">
                    @forelse ($entries as $e)<div class="flex items-center gap-3 p-3"><span class="grid size-10 place-items-center rounded-full {{ $e->type === 'credit' ? 'bg-emerald-100 text-forest-700' : 'bg-red-50 text-red-600' }}"><x-dynamic-component :component="$e->type === 'credit' ? 'lucide-arrow-down-left' : 'lucide-arrow-up-right'" class="size-5" /></span><span class="min-w-0 flex-1"><span class="block truncate font-medium">{{ $e->description }}</span><span class="text-xs text-muted">{{ $e->created_at->format('j M Y, g:ia') }}</span></span><span class="text-end"><span class="block font-semibold {{ $e->type === 'credit' ? 'text-forest-700' : '' }}">{{ $e->type === 'credit' ? '+' : '-' }}{{ \App\Support\Money::format($e->amount_kobo) }}</span><span class="text-xs text-muted">Bal {{ \App\Support\Money::format($e->balance_after_kobo) }}</span></span></div>@empty<x-ui.empty icon="receipt" title="No wallet activity yet" />@endforelse
                </div>{{ $entries->links() }}</section>
            <section><h2 class="mb-2 font-bold">Payments</h2>
                <div class="card divide-y divide-line">
                    @forelse ($payments as $p)<a href="{{ route('app.wallet.receipt', $p) }}" class="flex items-center gap-3 p-3 hover:bg-surface-2"><span class="min-w-0 flex-1"><span class="block font-medium">{{ ['wallet_topup' => 'Wallet top-up', 'subscription' => 'Subscription', 'event_ticket' => 'Event ticket'][$p->purpose] ?? $p->purpose }}</span><span class="text-xs text-muted">{{ $p->created_at->format('j M Y, g:ia') }} · {{ $p->reference }}</span></span><span class="text-end"><span class="block font-semibold">{{ \App\Support\Money::format($p->amount_kobo, $p->currency) }}</span><span class="badge {{ $p->status === 'succeeded' ? 'badge-check' : ($p->status === 'pending' ? 'badge-gold' : 'badge-x') }}">{{ ucfirst(str_replace('_', ' ', $p->status)) }}</span></span></a>@empty<x-ui.empty icon="credit-card" title="No payments yet" />@endforelse
                </div>{{ $payments->links() }}</section>
        </div>
    </div>
</x-layouts.app>
