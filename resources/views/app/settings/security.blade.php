<x-layouts.app title="Security">
    <x-slot:header><x-ui.page-header title="Security & devices" :back="route('app.settings.index')" /></x-slot:header>
    <div class="mx-auto max-w-xl space-y-4 py-5">
        <section class="card divide-y divide-line overflow-hidden">
            @foreach ($sessions as $s)
                <div class="flex items-center gap-3 p-4">
                    <span class="grid size-10 place-items-center rounded-full bg-mint text-forest-700"><x-lucide-smartphone class="size-5" /></span>
                    <div class="min-w-0 flex-1"><p class="font-semibold">{{ $s->agent }} @if ($s->current)<span class="badge ms-1">This device</span>@endif</p><p class="text-xs text-muted">{{ $s->ip }} · active {{ $s->last->diffForHumans() }}</p></div>
                </div>
            @endforeach
        </section>
        @if ($sessions->count() > 1)
            <form method="POST" action="{{ route('app.settings.security.sessions') }}" class="card space-y-3 p-4">@csrf @method('DELETE')
                <p class="font-semibold">Sign out of other devices</p>
                <x-ui.input name="password" type="password" label="Confirm your password" required />
                <x-ui.button type="submit" variant="outline" block>Sign out other devices</x-ui.button>
            </form>
        @endif
    </div>
</x-layouts.app>
