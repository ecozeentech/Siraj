<x-layouts.admin title="Notifications">
    @unless ($pushReady)<x-ui.alert type="warning">Push is not configured. In-app and email still send. Add VAPID keys under Settings before choosing push.</x-ui.alert>@endunless
    @if (session('preview') !== null)<x-ui.alert type="info">This audience currently has {{ number_format(session('preview')) }} members.</x-ui.alert>@endif
    <form method="POST" action="{{ route('admin.notifications.store') }}" class="max-w-2xl space-y-3 rounded-2xl border border-line bg-white p-5">@csrf
        <x-ui.input name="title" label="Title" required :value="old('title')" />
        <x-ui.textarea name="body" label="Message" rows="4" required :value="old('body')" />
        <x-ui.select name="category" label="Category" :options="\App\Models\NotificationPreference::CATEGORIES" :value="old('category', 'system')" />
        <x-ui.input name="url" label="Link (optional, starts with /app)" :value="old('url')" />
        <x-ui.select name="audience" label="Audience" :options="['all'=>'Everyone','plan'=>'A subscription plan','role'=>'A role']" :value="old('audience', 'all')" />
        <x-ui.select name="plan_id" label="Plan" :options="$plans->pluck('name','id')" placeholder="—" :value="old('plan_id')" />
        <x-ui.select name="role_id" label="Role" :options="$roles->pluck('name','id')" placeholder="—" :value="old('role_id')" />
        <fieldset><legend class="field-label">Channels</legend><div class="flex flex-wrap gap-4 text-sm">@foreach (['in_app'=>'In the app','email'=>'Email','push'=>'Push'] as $key => $label)<label class="flex items-center gap-2"><input type="checkbox" name="channels[]" value="{{ $key }}" @checked(in_array($key, old('channels', ['in_app']), true))>{{ $label }}</label>@endforeach</div></fieldset>
        <x-ui.input name="scheduled_at" type="datetime-local" label="Send at (leave empty to send now or save a draft)" :value="old('scheduled_at')" />
        <div class="flex flex-wrap gap-2">
            <x-ui.button type="submit" name="when" value="now">Send now</x-ui.button>
            <x-ui.button type="submit" name="when" value="schedule" variant="outline">Schedule</x-ui.button>
            <x-ui.button type="submit" name="when" value="draft" variant="ghost" formaction="{{ route('admin.notifications.store') }}">Save draft</x-ui.button>
            <button type="submit" class="btn btn-ghost btn-sm" formaction="{{ route('admin.notifications.preview') }}">Preview count</button>
        </div>
    </form>
    <section class="rounded-2xl border border-line bg-white"><table class="w-full text-sm"><thead class="bg-surface-2 text-xs uppercase text-muted"><tr><th class="px-4 py-3 text-start">Broadcast</th><th class="px-4 py-3 text-start">Status</th><th class="px-4 py-3 text-start">Reached</th></tr></thead>
        <tbody class="divide-y divide-line">@forelse ($broadcasts as $b)<tr><td class="px-4 py-3">{{ $b->title }}</td><td class="px-4 py-3">{{ $b->status }}</td><td class="px-4 py-3">{{ $b->delivered_count }}/{{ $b->recipients_count }}</td></tr>@empty<tr><td colspan="3" class="px-4 py-8 text-center text-muted">No broadcasts yet.</td></tr>@endforelse</tbody></table></section>
    {{ $broadcasts->links() }}
</x-layouts.admin>
