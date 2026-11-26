<x-layouts.app :title="$mosque->name">
    <x-slot:header><x-ui.page-header :title="$mosque->name" :subtitle="$mosque->city" :back="route('app.directory.index')" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-4 py-5">
        <section class="card space-y-3 p-5">
            @if ($mosque->verification_status === 'verified')<span class="badge badge-check"><x-lucide-badge-check class="size-3.5" />Details verified {{ $mosque->verified_at?->format('M Y') }}</span>@else<span class="badge badge-gold">Details not yet verified</span>@endif
            @if ($mosque->description)<p class="leading-7">{{ $mosque->description }}</p>@endif
            <dl class="divide-y divide-line text-sm">
                @foreach ([['Address', collect([$mosque->address, $mosque->city, $mosque->state, $mosque->country])->filter()->implode(', ')], ['Imam', $mosque->imam], ["Jumu'ah", $mosque->jummah_time], ['Phone', $mosque->phone]] as [$k, $v])@if ($v)<div class="flex justify-between gap-4 py-2.5"><dt class="text-muted">{{ $k }}</dt><dd class="text-end font-medium">{{ $v }}</dd></div>@endif @endforeach
                @if ($mosque->website)<div class="flex justify-between gap-4 py-2.5"><dt class="text-muted">Website</dt><dd><a class="font-medium text-forest-700 underline" href="{{ $mosque->website }}" target="_blank" rel="noopener noreferrer">Visit</a></dd></div>@endif
            </dl>
            @if ($mosque->facilities)<div class="flex flex-wrap gap-2">@foreach ($mosque->facilities as $f)<span class="badge">{{ \Illuminate\Support\Str::headline($f) }}</span>@endforeach</div>@endif
            <div class="flex flex-wrap gap-2">
                @if ($mosque->latitude && $mosque->longitude)<a class="btn btn-primary btn-sm" href="https://www.openstreetmap.org/directions?to={{ $mosque->latitude }},{{ $mosque->longitude }}" target="_blank" rel="noopener"><x-lucide-navigation class="size-4" />Directions</a>@endif
                @if ($mosque->phone)<a class="btn btn-outline btn-sm" href="tel:{{ preg_replace('/[^0-9+]/', '', $mosque->phone) }}"><x-lucide-phone class="size-4" />Call</a>@endif
            </div>
        </section>
        @include('app.directory._report', ['kind' => 'mosque', 'subject' => $mosque, 'options' => ['incorrect_info' => 'Incorrect information', 'closed' => 'Permanently closed', 'correction' => 'Suggest a correction (e.g. Jumu\'ah time)', 'other' => 'Other']])
    </div>
</x-layouts.app>
