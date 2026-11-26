<x-layouts.app title="{{ $resource->title }}">
    <x-slot:header><x-ui.page-header :title="$resource->title" :back="route('app.worship.tajweed')" /></x-slot:header>
    <article class="mx-auto max-w-2xl space-y-4 py-5">
        @if ($resource->example_ar)<div class="card p-6 text-center"><p class="quran-text text-4xl" dir="rtl">{{ $resource->example_ar }}</p><p class="mt-2 text-xs text-muted">Example</p></div>@endif
        <div class="card prose-siraj p-5">{!! \Illuminate\Support\Str::markdown($resource->body ?: (string) $resource->summary, ["html_input" => "strip", "allow_unsafe_links" => false]) !!}</div>
        @if ($resource->audio_url)<audio controls preload="none" class="w-full" src="{{ $resource->audio_url }}"></audio>@endif
    </article>
</x-layouts.app>
