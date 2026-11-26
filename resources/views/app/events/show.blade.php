<x-layouts.app :title="$event->title">
    <x-slot:header><x-ui.page-header :title="$event->title" :subtitle="$event->starts_at->format('D j M Y, g:ia')" :back="route('app.events.index')" /></x-slot:header>
    <div class="mx-auto grid max-w-5xl gap-5 py-5 lg:grid-cols-[1fr_20rem]">
        <div class="space-y-4">
            <x-art.cover :theme="$event->cover_theme" :src="$event->cover_path ? Storage::disk('public')->url($event->cover_path) : null" :alt="$event->title" class="h-52 w-full overflow-hidden rounded-3xl" />
            @if ($event->status === 'cancelled')<x-ui.alert type="danger" title="This event was cancelled" />@endif
            <section class="card space-y-3 p-5">
                <div class="flex flex-wrap gap-2"><span class="badge">{{ $event->category?->name ?? 'Event' }}</span><span class="badge badge-gold">{{ ['in_person' => 'In person', 'online' => 'Online', 'hybrid' => 'Hybrid'][$event->mode] }}</span></div>
                @if ($event->summary)<p class="text-lg font-medium">{{ $event->summary }}</p>@endif
                @if ($event->description)<div class="space-y-3 leading-7 text-muted">@foreach (preg_split('/\n\s*\n/', $event->description) as $p)<p>{{ $p }}</p>@endforeach</div>@endif
            </section>
            <section class="card divide-y divide-line">
                <div class="flex gap-3 p-4"><x-lucide-calendar class="mt-0.5 size-5 text-forest-700" /><div><p class="font-semibold">{{ $event->starts_at->format('l, j F Y') }}</p><p class="text-sm text-muted">{{ $event->starts_at->format('g:ia') }}@if ($event->ends_at) to {{ $event->ends_at->format('g:ia') }}@endif ({{ $event->timezone }})</p></div></div>
                @if ($event->mode !== 'online')<div class="flex gap-3 p-4"><x-lucide-map-pin class="mt-0.5 size-5 text-forest-700" /><div><p class="font-semibold">{{ $event->venue_name ?: 'Venue' }}</p><p class="text-sm text-muted">{{ collect([$event->address, $event->city])->filter()->implode(', ') ?: 'Details to be announced' }}</p>@if ($event->latitude && $event->longitude)<a class="text-sm font-semibold text-forest-700 underline" href="https://www.openstreetmap.org/?mlat={{ $event->latitude }}&mlon={{ $event->longitude }}#map=16/{{ $event->latitude }}/{{ $event->longitude }}" target="_blank" rel="noopener">View on map</a>@endif</div></div>@endif
                @if ($event->mode !== 'in_person' && $registration && $event->online_url)<div class="flex gap-3 p-4"><x-lucide-video class="mt-0.5 size-5 text-forest-700" /><div><p class="font-semibold">Join online</p><a href="{{ $event->online_url }}" class="break-all text-sm text-forest-700 underline" target="_blank" rel="noopener noreferrer">{{ $event->online_url }}</a></div></div>@endif
                @if ($event->organizer_name)<div class="flex gap-3 p-4"><x-lucide-user class="mt-0.5 size-5 text-forest-700" /><div><p class="text-sm text-muted">Organised by</p><p class="font-semibold">{{ $event->organizer_name }}</p></div></div>@endif
            </section>
        </div>
        <aside class="lg:sticky lg:top-6 lg:self-start">
            <section class="card space-y-4 p-5">
                <p class="text-2xl font-bold">{{ $event->isFree() ? 'Free' : \App\Support\Money::format($event->price_kobo, $event->currency) }}</p>
                @if ($event->capacity !== null)<p class="text-sm text-muted">{{ max(0, $event->capacity - $taken) }} of {{ $event->capacity }} places left</p>@endif
                @error('method')<x-ui.alert type="danger">{{ $message }}</x-ui.alert>@enderror
                @if ($registration)
                    <div class="rounded-2xl bg-mint p-4 text-center"><p class="text-sm font-semibold text-forest-800">{{ $registration->status === 'waitlisted' ? 'You are on the waitlist' : ($registration->status === 'pending_payment' ? 'Payment pending' : 'You are registered') }}</p>@if ($registration->status === 'registered' || $registration->status === 'attended')<p class="mt-1 font-mono text-lg font-bold tracking-wider">{{ $registration->ticket_code }}</p><p class="text-xs text-muted">Show this code at the door</p>@endif</div>
                    @if ($registration->status === 'pending_payment' && ! $past && $event->status === 'published')
                        <form method="POST" action="{{ route('app.events.register', $event) }}" class="space-y-2">@csrf<input type="hidden" name="method" value="gateway"><x-ui.button type="submit" block>Complete payment</x-ui.button></form>
                    @endif
                    @if (in_array($registration->status, ['registered', 'waitlisted', 'pending_payment']) && ! $past && ($event->isFree() || $registration->status !== 'registered'))
                        <x-ui.confirm :action="route('app.events.cancel', $event)" method="DELETE" message="Cancel your registration?" label="Cancel registration" variant="outline" class="text-center" />
                    @elseif (! $event->isFree() && $registration->status === 'registered')
                        <a href="{{ route('app.wallet.index') }}" class="block text-center text-sm font-semibold text-forest-700 underline">Need a refund? Request one from Wallet</a>
                    @endif
                @elseif ($past || $event->status !== 'published')
                    <p class="rounded-xl bg-surface-2 p-3 text-center text-sm text-muted">Registration is closed.</p>
                @elseif ($event->isFree())
                    <form method="POST" action="{{ route('app.events.register', $event) }}">@csrf<x-ui.button type="submit" block>{{ $event->capacity !== null && $taken >= $event->capacity ? 'Join waitlist' : 'Register for free' }}</x-ui.button></form>
                @else
                    <form method="POST" action="{{ route('app.events.register', $event) }}" class="space-y-3" x-data="{ m: '{{ $balance >= $event->price_kobo ? 'wallet' : 'gateway' }}' }">@csrf
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-line p-3" :class="m === 'wallet' ? 'border-forest-700 bg-mint' : ''"><input type="radio" name="method" value="wallet" x-model="m" class="text-forest-700"><span class="flex-1 text-sm"><span class="font-semibold">Pay from wallet</span><br><span class="text-muted">Balance {{ \App\Support\Money::format($balance) }}</span></span></label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-line p-3" :class="m === 'gateway' ? 'border-forest-700 bg-mint' : ''"><input type="radio" name="method" value="gateway" x-model="m" class="text-forest-700"><span class="flex-1 text-sm"><span class="font-semibold">Pay online</span><br><span class="text-muted">{{ $gatewayReady ? 'Card, bank transfer or USSD' : 'Not configured yet' }}</span></span></label>
                        <x-ui.button type="submit" block>Get ticket</x-ui.button>
                    </form>
                @endif
            </section>
        </aside>
    </div>
</x-layouts.app>
