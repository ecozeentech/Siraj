<x-layouts.kids :title="$course->title" :child="$child" :back="route('app.kids.home')">
    <h1 class="text-2xl font-bold">{{ $course->title }}</h1><p class="text-muted">{{ $course->summary }}</p>
    <div class="mt-4 space-y-3">
        @foreach ($course->lessons as $i => $l)
            <a href="{{ route('app.kids.lesson', [$course, $l]) }}" class="flex items-center gap-4 rounded-3xl bg-white p-4 shadow-card active:scale-[0.98]"><span class="grid size-12 place-items-center rounded-full text-lg font-bold {{ isset($done[$l->id]) ? 'bg-gold-300 text-forest-950' : 'bg-sky-100 text-sky-700' }}">@if (isset($done[$l->id]))<x-lucide-star class="size-6" fill="currentColor" />@else{{ $i + 1 }}@endif</span><span class="flex-1 text-lg font-bold">{{ $l->title }}</span><x-lucide-chevron-right class="size-5 text-muted" /></a>
        @endforeach
    </div>
</x-layouts.kids>
