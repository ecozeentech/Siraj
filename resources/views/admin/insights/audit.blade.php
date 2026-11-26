<x-layouts.admin title="Audit log">
    <form method="GET" class="flex flex-wrap gap-2">
        <x-ui.input name="q" :value="$q" placeholder="Action or description" class="min-w-56 flex-1" />
        <select name="category" class="field !w-auto"><option value="">All</option>@foreach (['admin','security','system'] as $c)<option value="{{ $c }}" @selected(request('category')===$c)>{{ $c }}</option>@endforeach</select>
        <x-ui.button type="submit" size="sm" variant="outline">Filter</x-ui.button>
    </form>
    <div class="overflow-x-auto rounded-2xl border border-line bg-white"><table class="w-full text-sm"><thead class="bg-surface-2 text-xs uppercase text-muted"><tr><th class="px-4 py-3 text-start">When</th><th class="px-4 py-3 text-start">Who</th><th class="px-4 py-3 text-start">Event</th></tr></thead>
        <tbody class="divide-y divide-line">@foreach ($logs as $log)<tr><td class="px-4 py-3 whitespace-nowrap text-xs text-muted">{{ $log->created_at->timezone('Africa/Lagos')->format('j M Y, g:ia') }}</td><td class="px-4 py-3">{{ $log->actor?->name ?? 'System' }}</td><td class="px-4 py-3"><p class="font-medium">{{ $log->action }}</p><p class="text-xs text-muted">{{ $log->description }}</p></td></tr>@endforeach</tbody></table></div>
    {{ $logs->links() }}
</x-layouts.admin>
