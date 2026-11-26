<x-layouts.public title="Verify certificate">
    <section class="mx-auto max-w-xl px-4 py-16 text-center">
        @if ($enrolment)
            <x-lucide-badge-check class="mx-auto size-14 text-forest-700" />
            <h1 class="mt-4 text-2xl font-bold">Valid certificate</h1>
            <p class="mt-3"><strong>{{ $enrolment->user->name }}</strong> completed <strong>{{ $enrolment->course->title }}</strong> on {{ $enrolment->completed_at->format('j F Y') }}.</p>
            <p class="mt-2 text-sm text-muted">Code {{ $code }}</p>
        @else
            <x-lucide-badge-x class="mx-auto size-14 text-red-600" />
            <h1 class="mt-4 text-2xl font-bold">Certificate not found</h1>
            <p class="mt-2 text-muted">We could not find a certificate with the code {{ $code }}. Check the code and try again.</p>
        @endif
    </section>
</x-layouts.public>
