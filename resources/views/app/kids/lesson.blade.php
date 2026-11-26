<x-layouts.kids :title="$lesson->title" :child="$child" :back="route('app.kids.course', $course)">
    <article class="rounded-3xl bg-white p-6 text-center shadow-card">
        <h1 class="text-2xl font-bold">{{ $lesson->title }}</h1>
        @if ($lesson->arabic_text)<p class="quran-text mt-4 text-5xl leading-[1.8]" dir="rtl" lang="ar">{{ $lesson->arabic_text }}</p>@endif
        @if ($lesson->transliteration)<p class="mt-2 text-lg font-semibold text-sky-700">{{ $lesson->transliteration }}</p>@endif
        <p class="mt-4 text-lg leading-8">{{ $lesson->body }}</p>
        @if ($lesson->audio_url)<audio controls class="mt-4 w-full" src="{{ $lesson->audio_url }}"></audio>@endif
    </article>
    <form method="POST" action="{{ route('app.kids.lesson.complete', [$course, $lesson]) }}" class="mt-5">@csrf<button class="btn btn-gold min-h-14 w-full text-lg"><x-lucide-star class="size-5" />I did it!</button></form>
</x-layouts.kids>
