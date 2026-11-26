<x-layouts.app title="Receipt">
    <x-slot:header><x-ui.page-header title="Receipt" :subtitle="$tx->reference" :back="route('app.wallet.index')" /></x-slot:header>
    <div class="mx-auto max-w-lg space-y-4 py-5">
        <section class="card space-y-3 p-5">
            <div class="text-center"><p class="text-3xl font-bold">{{ \App\Support\Money::format($tx->amount_kobo, $tx->currency) }}</p><span class="badge {{ $tx->status === 'succeeded' ? 'badge-check' : ($tx->status === 'pending' ? 'badge-gold' : 'badge-x') }} mt-2">{{ ucfirst(str_replace('_', ' ', $tx->status)) }}</span></div>
            <dl class="divide-y divide-line text-sm">
                @foreach ([['For', ['wallet_topup' => 'Wallet top-up', 'subscription' => 'Subscription: '.($payable->name ?? ''), 'event_ticket' => 'Event ticket: '.($payable->title ?? '')][$tx->purpose] ?? $tx->purpose], ['Paid with', $tx->gateway === 'wallet' ? 'Wallet' : ucfirst($tx->gateway).($tx->channel ? ' ('.$tx->channel.')' : '')], ['Date', ($tx->paid_at ?? $tx->created_at)->format('j M Y, g:ia')], ['Reference', $tx->reference], ['Mode', $tx->mode === 'sandbox' ? 'Test payment' : 'Live']] as [$k, $v])<div class="flex justify-between gap-4 py-2.5"><dt class="text-muted">{{ $k }}</dt><dd class="text-end font-medium break-all">{{ $v }}</dd></div>@endforeach
            </dl>
            @foreach ($tx->refunds as $r)<p class="rounded-xl bg-surface-2 p-3 text-sm">Refund of {{ \App\Support\Money::format($r->amount_kobo) }} to {{ $r->method }}: <strong>{{ ucfirst($r->status) }}</strong>@if ($r->failure_reason) ({{ $r->failure_reason }})@endif</p>@endforeach
            <button type="button" onclick="window.print()" class="btn btn-outline btn-sm print:hidden"><x-lucide-printer class="size-4" />Print</button>
        </section>
        @if (in_array($tx->status, ['succeeded', 'partially_refunded']) && in_array($tx->purpose, ['subscription', 'event_ticket']))
            <details class="card p-4"><summary class="cursor-pointer text-sm font-semibold">Request a refund</summary>
                <form method="POST" action="{{ route('app.wallet.refund', $tx) }}" class="mt-3 space-y-3">@csrf
                    <x-ui.select name="method" label="Refund to" :options="$tx->gateway === 'wallet' ? ['wallet' => 'My wallet'] : ['wallet' => 'My wallet (instant once approved)', 'gateway' => 'Original payment method']" />
                    <x-ui.textarea name="reason" label="Reason" rows="3" required maxlength="500" />
                    <x-ui.button type="submit" size="sm">Request refund</x-ui.button></form></details>
        @endif
    </div>
</x-layouts.app>
