<x-layouts.app title="Certificate">
    <x-slot:header><x-ui.page-header title="Certificate" :back="route('app.academy.course', $enrolment->course)" class="print:hidden" /></x-slot:header>
    <div class="mx-auto max-w-3xl py-6">
        <div class="relative overflow-hidden rounded-2xl border-[6px] border-double border-gold-400 bg-white p-8 text-center text-forest-950 shadow-card sm:p-12">
            <x-brand.mark class="mx-auto size-14" />
            <p class="mt-4 font-display text-sm uppercase tracking-[0.3em] text-gold-600">Certificate of Completion</p>
            <p class="mt-6 text-sm text-muted">This certifies that</p>
            <p class="mt-2 font-display text-3xl font-bold sm:text-4xl">{{ $enrolment->user->name }}</p>
            <p class="mt-4 text-sm text-muted">has successfully completed the course</p>
            <p class="mt-2 text-2xl font-bold text-forest-700">{{ $enrolment->course->title }}</p>
            <p class="mt-6 text-sm">{{ $enrolment->completed_at->format('j F Y') }}</p>
            <p class="mt-6 text-xs text-muted">Verify at {{ route('certificate.verify', $enrolment->certificate_code) }}<br>Code: {{ $enrolment->certificate_code }}</p>
        </div>
        <div class="mt-4 flex justify-center gap-3 print:hidden"><button type="button" class="btn btn-primary" onclick="window.print()"><x-lucide-printer class="size-4" />Print or save as PDF</button></div>
    </div>
</x-layouts.app>
