<x-layouts.admin title="Referrals">
    <form method="POST" action="{{ route('admin.referrals.update') }}" class="space-y-3 rounded-2xl border border-line bg-white p-5">@csrf @method('PUT')
        <h2 class="font-bold">Reward rules</h2>
        <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="enabled" value="1" @checked($enabled)> Referrals are open</label>
        <div class="grid gap-3 sm:grid-cols-3">
            <x-ui.input name="points_per_signup" type="number" min="0" label="Points when a friend joins" :value="$points" />
            <x-ui.input name="points_to_redeem" type="number" min="1" label="Points to redeem a plan" :value="$cost" />
            <label class="block text-sm font-semibold">Plan granted on redeem
                <select name="plan_id" class="field mt-1">
                    <option value="">First paid plan</option>
                    @foreach ($plans as $plan)<option value="{{ $plan->id }}" @selected((string) $planId === (string) $plan->id)>{{ $plan->name }}</option>@endforeach
                </select>
            </label>
        </div>
        <x-ui.button type="submit">Save rewards</x-ui.button>
    </form>
    <section class="overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full text-sm">
            <thead class="bg-surface-2 text-start text-xs uppercase"><tr><th class="px-4 py-3">Member</th><th class="px-4 py-3">Code</th><th class="px-4 py-3">Points</th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($accounts as $account)
                    <tr><td class="px-4 py-3">{{ $account->user?->name }}<br><span class="text-xs">{{ $account->user?->email }}</span></td><td class="px-4 py-3 font-semibold">{{ $account->code }}</td><td class="px-4 py-3">{{ $account->balance }}</td></tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center">No referral accounts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
    {{ $accounts->links() }}
</x-layouts.admin>
