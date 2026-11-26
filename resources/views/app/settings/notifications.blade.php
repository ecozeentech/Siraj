<x-layouts.app title="Notification settings">
    <x-slot:header><x-ui.page-header title="Notifications" :back="route('app.settings.index')" /></x-slot:header>
    <div class="mx-auto max-w-xl space-y-4 py-5" x-data="pushSetup()">
        <section class="card space-y-3 p-4">
            <p class="font-semibold">Push notifications on this device</p>
            @if (! $pushReady)
                <x-ui.alert type="warning" title="Not available yet">Web push has not been configured by the administrator, so push notifications can't be enabled. In-app and email notifications work normally.</x-ui.alert>
            @else
                <p class="text-sm text-muted" x-text="message"></p>
                <x-ui.button type="button" variant="outline" size="sm" x-on:click="toggle()" x-text="enabled ? 'Disable on this device' : 'Enable on this device'" x-bind:disabled="!supported"></x-ui.button>
            @endif
        </section>
        <form method="POST" action="{{ route('app.settings.notifications.update') }}" class="space-y-4">@csrf @method('PUT')
            <div class="card overflow-hidden">
                <div class="grid grid-cols-[1fr_repeat(3,3.5rem)] items-center gap-y-0 border-b border-line bg-surface-2 px-4 py-2 text-center text-[11px] font-semibold uppercase tracking-wide text-muted"><span class="text-start">Category</span><span>In-app</span><span>Email</span><span>Push</span></div>
                @foreach ($categories as $key => $label)
                    @php($p = $prefs[$key] ?? null)
                    <div class="grid grid-cols-[1fr_repeat(3,3.5rem)] items-center border-b border-line px-4 py-3 last:border-0">
                        <span class="text-sm font-medium">{{ $label }}</span>
                        @foreach (['in_app' => true, 'email' => false, 'push' => false] as $ch => $default)
                            <label class="grid place-items-center"><input type="checkbox" name="prefs[{{ $key }}][{{ $ch }}]" value="1" @checked($p ? $p->{$ch} : $default) @disabled($key === 'system' && $ch === 'in_app') class="size-5 rounded-md border-line text-forest-700"></label>
                        @endforeach
                    </div>
                @endforeach
            </div>
            <x-ui.button type="submit" block>Save preferences</x-ui.button>
        </form>
    </div>
</x-layouts.app>
