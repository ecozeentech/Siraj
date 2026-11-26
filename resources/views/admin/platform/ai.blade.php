<x-layouts.admin title="SIRAJ AI">
    <x-ui.alert :type="$connected ? 'success' : 'warning'">{{ $connected ? 'A provider with a key is active. Answers still only cite retrieved sources.' : 'No provider is configured. Members see retrieved sources and an honest “needs configuration” notice. Add a provider below — the key is stored encrypted and never shown again.' }}</x-ui.alert>
    <div class="flex flex-wrap gap-3">
        @foreach (['ai-providers' => 'Providers', 'ai-models' => 'Models', 'ai-sources' => 'Knowledge sources'] as $key => $label)
            @can('ai.manage')<x-ui.button :href="route('admin.ai.'.$key.'.index')" variant="outline" size="sm">{{ $label }}</x-ui.button>@endcan
        @endforeach
    </div>
    <section class="rounded-2xl border border-line bg-white p-5">
        <h2 class="font-bold">Usage</h2>
        <table class="mt-3 w-full text-sm"><thead><tr class="text-start text-xs uppercase text-muted"><th class="py-2 text-start">Status</th><th class="text-start">Calls</th><th class="text-start">Tokens in</th><th class="text-start">Tokens out</th><th class="text-start">Cost (USD)</th></tr></thead>
            <tbody>@forelse ($byStatus as $row)<tr><td class="py-1">{{ $row->status }}</td><td>{{ $row->c }}</td><td>{{ $row->tin }}</td><td>{{ $row->tout }}</td><td>{{ $row->cost }}</td></tr>@empty<tr><td colspan="5" class="py-4 text-muted">No usage recorded.</td></tr>@endforelse</tbody></table>
    </section>
</x-layouts.admin>
