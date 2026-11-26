<x-layouts.kids :title="$story->title" :child="$child" :back="route('app.kids.home')">
    <x-art.cover :theme="$story->cover_theme" class="h-44 w-full rounded-3xl" />
    <article class="mt-4 rounded-3xl bg-white p-6 shadow-card"><h1 class="text-2xl font-bold">{{ $story->title }}</h1>@if ($story->reference)<p class="text-sm text-muted">From {{ $story->reference }}</p>@endif
        <div class="mt-4 space-y-4 text-lg leading-8">@foreach (preg_split('/\n\s*\n/', $story->body) as $p)<p>{{ $p }}</p>@endforeach</div>
        @if ($story->moral)<p class="mt-5 rounded-2xl bg-gold-100 p-4 text-center font-bold text-[#6b5314]">Lesson: {{ $story->moral }}</p>@endif
    </article>
</x-layouts.kids>
