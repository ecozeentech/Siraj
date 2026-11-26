<x-layouts.admin :title="$member->name">
    <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-forest-700">All members</a>
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="space-y-3 rounded-2xl border border-line bg-white p-5 text-sm">
            <h2 class="font-bold">Account</h2>
            <p>{{ $member->email }} · {{ $member->status }}{{ $member->is_staff ? ' · staff' : '' }}</p>
            <p class="text-muted">Joined {{ $member->created_at->toFormattedDateString() }} · last sign-in {{ $member->last_login_at?->diffForHumans() ?? 'never' }}</p>
            <p>Wallet {{ \App\Support\Money::format((int) ($member->wallet->balance_kobo ?? 0)) }} · plan {{ $member->activeSubscription?->plan?->name ?? 'none' }}</p>
            @if ($member->suspension_reason)<p>Suspension: {{ $member->suspension_reason }}</p>@endif
            @can('users.manage')
                <form method="POST" action="{{ route('admin.users.update', $member) }}" class="space-y-3 border-t border-line pt-3">@csrf
                    <x-ui.input name="name" label="Name" :value="$member->name" />
                    <fieldset><legend class="field-label">Roles</legend><div class="space-y-1">@foreach ($roles as $role)<label class="flex items-center gap-2"><input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked($member->roles->contains('id', $role->id))>{{ $role->name }}</label>@endforeach</div></fieldset>
                    <x-ui.button type="submit" size="sm">Save</x-ui.button>
                </form>
                @if (auth('admin')->user()->isSuperAdmin())<form method="POST" action="{{ route('admin.users.staff', $member) }}" class="pt-2">@csrf<button class="text-sm font-semibold text-forest-700">{{ $member->is_staff ? 'Remove staff access' : 'Grant staff access' }}</button></form>@endif
            @endcan
        </section>
        <section class="space-y-3 rounded-2xl border border-line bg-white p-5 text-sm">
            <h2 class="font-bold">Safety</h2>
            @can('users.suspend')
                @if ($member->status === 'suspended')
                    <form method="POST" action="{{ route('admin.users.activate', $member) }}">@csrf<x-ui.button type="submit" size="sm">Reactivate</x-ui.button></form>
                @else
                    <form method="POST" action="{{ route('admin.users.suspend', $member) }}" class="space-y-2">@csrf<x-ui.input name="reason" label="Suspension reason" /><x-ui.button type="submit" variant="danger" size="sm">Suspend and sign out</x-ui.button></form>
                @endif
            @endcan
            @can('users.delete')
                <form method="POST" action="{{ route('admin.users.deletion', $member) }}">@csrf<x-ui.button type="submit" variant="outline" size="sm">{{ $member->status === 'pending_deletion' ? 'Cancel deletion' : 'Schedule deletion (14 days)' }}</x-ui.button></form>
            @endcan
            <h3 class="pt-2 font-semibold">Restrictions</h3>
            <ul>@forelse ($restrictions as $r)<li>{{ $r->type }} · {{ $r->reason }} {{ $r->lifted_at ? '(lifted)' : '' }}</li>@empty<li class="text-muted">None.</li>@endforelse</ul>
        </section>
    </div>
</x-layouts.admin>
