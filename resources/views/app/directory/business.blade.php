<x-layouts.app :title="$business->name">
    <x-slot:header><x-ui.page-header :title="$business->name" :subtitle="($business->category?->name ?? 'Business').' · '.$business->city" :back="route('app.directory.index', ['tab' => 'businesses'])" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-4 py-5">
        <section class="card space-y-3 p-5">
            @if ($halalValid)
                <div class="rounded-2xl bg-emerald-100 p-3 text-sm text-forest-800"><p class="flex items-center gap-2 font-semibold"><x-lucide-badge-check class="size-5" />Halal certificate checked by SIRAJ staff</p><p class="mt-1">Certifier: {{ $business->halal_certifier ?: 'Not recorded' }}@if ($business->halal_certificate_number) · Certificate {{ $business->halal_certificate_number }}@endif @if ($business->halal_certificate_expires_at) · Valid until {{ $business->halal_certificate_expires_at->format('j M Y') }}@endif</p></div>
            @else
                <div class="rounded-2xl bg-gold-100 p-3 text-sm text-[#6b5314]"><p class="flex items-center gap-2 font-semibold"><x-lucide-info class="size-5" />Halal status is not verified</p><p class="mt-1">SIRAJ has not verified a halal certificate for this business. Please confirm directly with the business if this matters to you.</p></div>
            @endif
            @if ($business->description)<p class="leading-7">{{ $business->description }}</p>@endif
            <dl class="divide-y divide-line text-sm">
                @foreach ([['Address', collect([$business->address, $business->city, $business->state])->filter()->implode(', ')], ['Phone', $business->phone], ['Email', $business->email]] as [$k, $v])@if ($v)<div class="flex justify-between gap-4 py-2.5"><dt class="text-muted">{{ $k }}</dt><dd class="text-end font-medium break-all">{{ $v }}</dd></div>@endif @endforeach
                @if ($business->website)<div class="flex justify-between gap-4 py-2.5"><dt class="text-muted">Website</dt><dd><a class="font-medium text-forest-700 underline" href="{{ $business->website }}" target="_blank" rel="noopener noreferrer">Visit</a></dd></div>@endif
            </dl>
            <p class="text-sm"><x-lucide-star class="inline size-4 text-gold-500" fill="#e4b960" /> <strong>{{ $business->reviews_count ? number_format($business->rating_average, 1) : 'No ratings yet' }}</strong> @if ($business->reviews_count)<span class="text-muted">({{ $business->reviews_count }} reviews)</span>@endif</p>
        </section>
        <section class="card space-y-3 p-5"><h2 class="font-bold">{{ $mine ? 'Update your review' : 'Write a review' }}</h2>
            @if ($business->submitted_by === auth()->id())<p class="text-sm text-muted">You can't review a business you submitted.</p>@else
            <form method="POST" action="{{ route('app.directory.review', $business) }}" class="space-y-3" x-data="{ r: {{ $mine->rating ?? 0 }} }">@csrf<input type="hidden" name="rating" :value="r">
                <div class="flex gap-1" role="radiogroup" aria-label="Rating">@for ($i = 1; $i <= 5; $i++)<button type="button" role="radio" :aria-checked="r === {{ $i }}" @click="r = {{ $i }}" class="grid size-10 place-items-center" aria-label="{{ $i }} star{{ $i > 1 ? 's' : '' }}"><x-lucide-star class="size-7" x-bind:class="r >= {{ $i }} ? 'text-gold-400' : 'text-line'" x-bind:fill="r >= {{ $i }} ? 'currentColor' : 'none'" /></button>@endfor</div>
                @error('rating')<p class="field-error">Please choose a rating.</p>@enderror
                <x-ui.textarea name="body" rows="3" maxlength="1000" :value="$mine->body ?? ''" placeholder="Share your experience (optional)" />
                <x-ui.button type="submit">{{ $mine ? 'Update review' : 'Submit review' }}</x-ui.button></form>@endif
        </section>
        <section class="space-y-3"><h2 class="font-bold">Reviews</h2>
            @forelse ($reviews as $r)<div class="card p-4"><div class="flex items-center gap-3"><x-ui.avatar :user="$r->user" size="sm" /><div class="flex-1"><p class="font-semibold">{{ $r->user->name }}</p><p class="text-xs text-muted">{{ $r->created_at->diffForHumans() }}</p></div><span class="text-sm font-bold"><x-lucide-star class="inline size-4 text-gold-500" fill="#e4b960" /> {{ $r->rating }}</span></div>@if ($r->body)<p class="mt-2 whitespace-pre-line">{{ $r->body }}</p>@endif</div>@empty<p class="text-sm text-muted">No reviews yet.</p>@endforelse
            {{ $reviews->links() }}
        </section>
        @include('app.directory._report', ['kind' => 'business', 'subject' => $business, 'options' => ['incorrect_info' => 'Incorrect information', 'closed' => 'Permanently closed', 'not_halal' => 'Halal claim seems wrong', 'correction' => 'Suggest a correction', 'other' => 'Other']])
    </div>
</x-layouts.app>
