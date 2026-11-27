<x-layouts.admin title="Payments">
    <section class="rounded-2xl border border-line bg-white p-5">
        <h2 class="font-bold">Manual wallet adjustment</h2>
        <p class="mt-1 text-sm text-muted">Posts a balanced ledger entry against the adjustments account. It does not pretend a Paystack payment happened.</p>
        @can('finance.adjust')
            <form method="POST" action="{{ route('admin.payments.adjust') }}" class="mt-3 grid gap-3 sm:grid-cols-2">@csrf
                <x-ui.input name="email" type="email" label="Member email" />
                <x-ui.select name="direction" label="Direction" :options="['credit'=>'Credit the wallet','debit'=>'Debit the wallet']" />
                <x-ui.input name="amount" type="number" step="0.01" min="1" label="Amount (NGN)" />
                <x-ui.input name="reason" label="Reason (recorded in the audit log)" />
                <x-ui.button type="submit" class="sm:col-span-2 sm:w-fit">Post adjustment</x-ui.button>
            </form>
        @endcan
    </section>
    <section>
        <div class="mb-2 flex gap-2 text-sm">@foreach (['' => 'All', 'succeeded' => 'Succeeded', 'pending' => 'Pending', 'failed' => 'Failed'] as $k => $label)<a class="chip {{ request('status','')===$k ? 'chip-active' : '' }}" href="{{ route('admin.payments.index', $k ? ['status'=>$k] : []) }}">{{ $label }}</a>@endforeach</div>
        <div class="overflow-x-auto rounded-2xl border border-line bg-white"><table class="w-full text-sm"><thead class="bg-surface-2 text-xs uppercase text-muted"><tr><th class="px-4 py-3 text-start">Payment</th><th class="px-4 py-3 text-start">Amount</th><th class="px-4 py-3 text-start">Status</th></tr></thead>
            <tbody class="divide-y divide-line">@forelse ($payments as $p)<tr><td class="px-4 py-3"><p class="font-medium">{{ $p->purpose }}</p><p class="text-xs text-muted">{{ $p->user?->email }} · {{ $p->reference }}</p></td><td class="px-4 py-3">{{ \App\Support\Money::format($p->amount_kobo) }}</td><td class="px-4 py-3">{{ $p->status }}@if ($p->status === 'pending') @can('finance.adjust')<form method="POST" action="{{ route('admin.payments.confirm', $p) }}" class="mt-1">@csrf<button class="text-xs font-semibold underline">Confirm receipt</button></form>@endcan @endif</td></tr>@empty<tr><td colspan="3" class="px-4 py-8 text-center text-muted">No payments.</td></tr>@endforelse</tbody></table></div>
        {{ $payments->links() }}
    </section>
    <section class="space-y-3">
        <h2 class="font-bold">Refund requests</h2>
        @forelse ($refunds as $refund)
            <article class="rounded-2xl border border-line bg-white p-4 text-sm">
                <p class="font-medium">{{ \App\Support\Money::format($refund->amount_kobo) }} · {{ $refund->status }} · {{ $refund->transaction?->purpose }}</p>
                <p class="text-muted">{{ $refund->reason }} · {{ $refund->transaction?->user?->email }}</p>
                @if ($refund->status === 'requested')
                    @can('payments.refund')
                        <div class="mt-2 flex gap-2">
                            <form method="POST" action="{{ route('admin.payments.refund', $refund) }}">@csrf<input type="hidden" name="decision" value="process"><button class="btn btn-primary btn-sm">Refund to wallet or gateway</button></form>
                            <form method="POST" action="{{ route('admin.payments.refund', $refund) }}" class="flex gap-2">@csrf<input type="hidden" name="decision" value="reject"><input name="why" placeholder="Why" class="field !w-48"><button class="btn btn-outline btn-sm">Decline</button></form>
                        </div>
                    @endcan
                @endif
            </article>
        @empty
            <p class="text-sm text-muted">No refund requests.</p>
        @endforelse
        {{ $refunds->links() }}
    </section>
</x-layouts.admin>
