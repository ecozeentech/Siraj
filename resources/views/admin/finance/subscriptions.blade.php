<x-layouts.admin title="Subscriptions">
    @can('subscriptions.manage')<x-ui.button :href="route('admin.subscriptions.plans.index')" size="sm" variant="outline">Edit plans</x-ui.button>@endcan
    <div class="overflow-x-auto rounded-2xl border border-line bg-white"><table class="w-full text-sm"><thead class="bg-surface-2 text-xs uppercase text-muted"><tr><th class="px-4 py-3 text-start">Member</th><th class="px-4 py-3 text-start">Plan</th><th class="px-4 py-3 text-start">Status</th><th class="px-4 py-3 text-start">Renews</th></tr></thead>
        <tbody class="divide-y divide-line">@foreach ($subscriptions as $sub)<tr><td class="px-4 py-3">{{ $sub->user?->email }}</td><td class="px-4 py-3">{{ $sub->plan?->name }}</td><td class="px-4 py-3">{{ $sub->status }}</td><td class="px-4 py-3">{{ $sub->ends_at?->toFormattedDateString() ?? '—' }}</td></tr>@endforeach</tbody></table></div>
    {{ $subscriptions->links() }}
</x-layouts.admin>
