<x-layouts.admin title="Roles">
    <form method="POST" action="{{ route('admin.roles.store') }}" class="flex flex-wrap items-end gap-3 rounded-2xl border border-line bg-white p-4">@csrf
        <x-ui.input name="name" label="New role" class="min-w-48" />
        <x-ui.input name="description" label="Description" class="min-w-64" />
        <x-ui.button type="submit" size="sm">Create</x-ui.button>
    </form>
    <div class="space-y-4">
        @foreach ($roles as $role)
            <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="rounded-2xl border border-line bg-white p-4">@csrf @method('PUT')
                <div class="flex flex-wrap items-end justify-between gap-3"><div><h2 class="font-bold">{{ $role->name }}</h2><p class="text-xs text-muted">{{ $role->slug }} · {{ $role->users_count }} people{{ $role->is_system ? ' · system role' : '' }}</p></div>@unless($role->slug === 'super-admin')<x-ui.button type="submit" size="sm">Save permissions</x-ui.button>@endunless</div>
                @if ($role->slug === 'super-admin')
                    <p class="mt-2 text-sm text-muted">This role always has every permission and cannot be edited.</p>
                @else
                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @unless($role->is_system)<x-ui.input name="name" :value="$role->name" label="Name" class="sm:col-span-2" /><x-ui.input name="description" :value="$role->description" label="Description" class="sm:col-span-2 lg:col-span-3" />@else<input type="hidden" name="name" value="{{ $role->name }}"><input type="hidden" name="description" value="{{ $role->description }}">@endunless
                        @foreach ($catalogue as $group => $permissions)
                            <p class="sm:col-span-2 lg:col-span-3 mt-2 text-xs font-semibold uppercase tracking-wide text-muted">{{ $group }}</p>
                            @foreach ($permissions as $slug => $label)
                                <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="permissions[]" value="{{ $slug }}" @checked($role->permissions->contains('slug', $slug))><span>{{ $label }}<span class="block text-xs text-muted">{{ $slug }}</span></span></label>
                            @endforeach
                        @endforeach
                    </div>
                @endif
            </form>
        @endforeach
    </div>
</x-layouts.admin>
