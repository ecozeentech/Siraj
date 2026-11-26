<x-layouts.app title="Tajweed">
    <x-slot:header><x-ui.page-header title="Tajweed guide" subtitle="Rules for beautiful recitation" :back="route('app.quran.index')" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-3 py-5">
        @forelse ($resources as $r)
            <a href="{{ route('app.worship.tajweed.show', $r->slug) }}" class="card flex items-center gap-3 p-4"><span class="min-w-0 flex-1"><span class="block font-semibold">{{ $r->title }}</span><span class="block text-sm text-muted">{{ $r->summary }}</span></span>@if ($r->example_ar)<span class="font-quran text-2xl" dir="rtl">{{ $r->example_ar }}</span>@endif</a>
        @empty
            <x-ui.empty icon="mic" title="Tajweed lessons are coming" text="Our team is preparing reviewed lessons." />
        @endforelse
    </div>
</x-layouts.app>
