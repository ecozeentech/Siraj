<x-layouts.admin title="Members">
    <form method="GET" class="flex flex-wrap gap-2">
        <x-ui.input name="q" :value="$q" placeholder="Name or email" class="min-w-56 flex-1" />
        <select name="status" class="field !w-auto"><option value="">Any status</option>@foreach (['active','suspended','pending_deletion'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ str_replace('_',' ',$s) }}</option>@endforeach</select>
        <x-ui.button type="submit" size="sm" variant="outline">Search</x-ui.button>
    </form>
    <div class="overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full text-sm"><thead class="bg-surface-2 text-xs uppercase text-muted"><tr><th class="px-4 py-3 text-start">Member</th><th class="px-4 py-3 text-start">Status</th><th class="px-4 py-3 text-start">Roles</th><th></th></tr></thead>
            <tbody class="divide-y divide-line">@foreach ($users as $user)<tr><td class="px-4 py-3"><p class="font-medium">{{ $user->name }}</p><p class="text-xs text-muted">{{ $user->email }}</p></td><td class="px-4 py-3"><span class="badge">{{ str_replace('_',' ',$user->status) }}</span></td><td class="px-4 py-3">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td><td class="px-4 py-3 text-end"><a class="font-semibold text-forest-700" href="{{ route('admin.users.show', $user) }}">Open</a></td></tr>@endforeach</tbody>
        </table>
    </div>
    {{ $users->links() }}
</x-layouts.admin>
