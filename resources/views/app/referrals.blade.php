<x-layouts.app title="Refer and earn">
    <x-slot:header><x-ui.page-header title="Refer & Earn" subtitle="Points you can redeem for a plan" /></x-slot:header>
    <div class="mx-auto max-w-xl space-y-4 py-5">
        @error('redeem')<x-ui.alert type="danger">{{ $message }}</x-ui.alert>@enderror
        <section class="card p-5">
            <p class="text-sm font-semibold text-ink">Your points</p>
            <p class="text-4xl font-bold">{{ number_format($account->balance) }}</p>
            <p class="mt-3 text-sm text-ink">Share this link. When someone creates an account with it, you receive points.</p>
            <p class="mt-3 break-all rounded-xl bg-surface-2 px-3 py-3 font-semibold text-ink">{{ $link }}</p>
            <p class="mt-2 text-sm font-semibold">Code {{ $account->code }}</p>
        </section>
        @if ($enabled && $plan)
            <section class="card p-5">
                <h2 class="font-bold">Redeem {{ number_format($cost) }} points</h2>
                <p class="mt-1 text-sm text-ink">This activates {{ $plan->name }} without a cash charge. Points are not money and cannot be withdrawn.</p>
                <form method="POST" action="{{ route('app.referrals.redeem') }}" class="mt-4">@csrf
                    <x-ui.button type="submit" block :disabled="$account->balance < $cost">Redeem for {{ $plan->name }}</x-ui.button>
                </form>
            </section>
        @else
            <x-ui.alert type="info">An administrator has not opened redemptions yet.</x-ui.alert>
        @endif
        <section>
            <h2 class="mb-2 font-bold">History</h2>
            <div class="card divide-y divide-line">
                @forelse ($entries as $entry)
                    <div class="flex items-center justify-between px-4 py-3"><span class="font-medium">{{ $entry->reason === 'signup' ? 'Friend joined' : 'Redeemed' }}</span><span class="font-bold {{ $entry->points > 0 ? 'text-[#128a49]' : 'text-ink' }}">{{ $entry->points > 0 ? '+' : '' }}{{ $entry->points }}</span></div>
                @empty
                    <p class="px-4 py-6 text-sm text-ink">No points yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
