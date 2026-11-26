<x-layouts.admin :title="$def['label']">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.'.$def['module'].'.index') }}" class="text-sm font-semibold text-forest-700">{{ \App\Admin\Registry::MODULES[$def['module']][0] ?? 'Back' }}</a>
            <h2 class="text-lg font-bold">{{ $def['label'] }}</h2>
        </div>
        <x-ui.button :href="route('admin.'.$def['module'].'.'.$resource.'.create')" size="sm" icon="plus">Add</x-ui.button>
    </div>
    @if (! empty($def['note']))<x-ui.alert type="info">{{ $def['note'] }}</x-ui.alert>@endif
    <form method="GET" class="flex flex-wrap gap-2">
        @if (! empty($def['search']))<x-ui.input name="q" :value="request('q')" placeholder="Search" class="min-w-56 flex-1" />@endif
        @foreach ($def['filters'] ?? [] as [$col, $label, $options])
            <select name="f[{{ $col }}]" class="field !w-auto" aria-label="{{ $label }}"><option value="">{{ $label }}</option>@foreach ($options as $k => $v)<option value="{{ $k }}" @selected(request('f.'.$col) === $k)>{{ $v }}</option>@endforeach</select>
        @endforeach
        <x-ui.button type="submit" size="sm" variant="outline">Filter</x-ui.button>
    </form>
    <div class="overflow-x-auto rounded-2xl border border-line bg-white">
        <table class="w-full text-sm">
            <thead class="bg-surface-2 text-start text-xs uppercase tracking-wide text-muted"><tr>@foreach ($def['columns'] as $col)<th class="px-4 py-3 text-start font-semibold">{{ $col[1] }}</th>@endforeach<th class="px-4 py-3"></th></tr></thead>
            <tbody class="divide-y divide-line">
                @forelse ($rows as $row)
                    <tr>
                        @foreach ($def['columns'] as $col)
                            @php($format = $col[2] ?? 'text')
                            @php($value = data_get($row, $col[0]))
                            <td class="px-4 py-3 align-top">
                                @if ($format === 'bool')<span class="badge {{ $value ? 'badge-check' : '' }}">{{ $value ? 'Yes' : 'No' }}</span>
                                @elseif ($format === 'money'){{ \App\Support\Money::format((int) $value) }}
                                @elseif ($format === 'datetime'){{ $value ? \Carbon\Carbon::parse($value)->timezone('Africa/Lagos')->format('j M Y, g:ia') : '—' }}
                                @elseif ($format === 'badge')<span class="badge">{{ ucfirst(str_replace('_', ' ', (string) $value)) }}</span>
                                @elseif ($format === 'secret_state')<span class="badge {{ filled($value) ? 'badge-check' : 'badge-gold' }}">{{ filled($value) ? 'Set' : 'Not set' }}</span>
                                @else<span class="line-clamp-2">{{ $value === null || $value === '' ? '—' : \Illuminate\Support\Str::limit(strip_tags((string) (is_array($value) ? json_encode($value) : $value)), 90) }}</span>@endif
                            </td>
                        @endforeach
                        <td class="px-4 py-3 text-end whitespace-nowrap">
                            <a class="font-semibold text-forest-700" href="{{ route('admin.'.$def['module'].'.'.$resource.'.edit', $row->id) }}">Edit</a>
                            @foreach ($def['actions'] ?? [] as [$action, $label, $perm])
                                @if (auth('admin')->user()->hasPermission($perm))
                                    <form method="POST" action="{{ route('admin.'.$def['module'].'.'.$resource.'.action', [$row->id, $action]) }}" class="inline" onsubmit="return confirm(@js($label.'?'))">@csrf<button class="ms-2 font-semibold text-forest-700">{{ $label }}</button></form>
                                @endif
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($def['columns']) + 1 }}" class="px-4 py-10 text-center text-muted">Nothing here yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $rows->links() }}
</x-layouts.admin>
