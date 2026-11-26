<x-layouts.app title="{{ $lesson->title }}">
    <x-slot:header><x-ui.page-header :title="$lesson->title" :subtitle="'Lesson '.$index.' of '.$total.' · '.$course->title" :back="route('app.academy.course', $course)" /></x-slot:header>
    <div class="mx-auto max-w-3xl space-y-4 py-5" x-data="lessonPlayer({ url: @js(route('app.academy.lesson.position', $lesson)), position: {{ (int) ($progress->position_seconds ?? 0) }}, enabled: {{ $enrolled ? 'true' : 'false' }} })">
        @if ($lesson->type === 'video' && ($lesson->media_url || $lesson->media_path))
            <video x-ref="media" controls preload="metadata" class="aspect-video w-full rounded-2xl bg-black" src="{{ $lesson->media_path ? \Illuminate\Support\Facades\Storage::url($lesson->media_path) : $lesson->media_url }}"></video>
        @elseif ($lesson->type === 'audio' && ($lesson->media_url || $lesson->media_path))
            <audio x-ref="media" controls preload="metadata" class="w-full" src="{{ $lesson->media_path ? \Illuminate\Support\Facades\Storage::url($lesson->media_path) : $lesson->media_url }}"></audio>
        @endif
        @if ($lesson->body)<article class="card prose-siraj p-5">{!! \Illuminate\Support\Str::markdown($lesson->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</article>@endif
        @if ($lesson->resources->isNotEmpty())
            <section class="card divide-y divide-line overflow-hidden"><h2 class="px-4 py-3 font-bold">Resources</h2>@foreach ($lesson->resources as $r)<a href="{{ $r->path ? \Illuminate\Support\Facades\Storage::url($r->path) : $r->url }}" target="_blank" rel="noopener noreferrer" class="list-row"><x-lucide-paperclip class="size-4 text-muted" /><span class="flex-1">{{ $r->title }}</span><x-lucide-external-link class="size-4 text-muted" /></a>@endforeach</section>
        @endif
        @foreach ($lesson->quizzes as $q)@if ($enrolled)<a href="{{ route('app.academy.quiz', $q) }}" class="card flex items-center gap-3 p-4"><x-lucide-circle-help class="size-5 text-forest-700" /><span class="flex-1 font-semibold">Test yourself: {{ $q->title }}</span><x-lucide-arrow-right class="size-4" /></a>@endif @endforeach
        <div class="flex items-center gap-3">
            @if ($prev)<x-ui.button :href="route('app.academy.lesson', [$course, $prev])" variant="outline">Previous</x-ui.button>@endif
            @if ($enrolled)
                <form method="POST" action="{{ route('app.academy.lesson.complete', [$course, $lesson]) }}" class="flex-1">@csrf<x-ui.button type="submit" block>{{ $progress?->completed_at ? 'Completed — continue' : 'Mark complete & continue' }}</x-ui.button></form>
            @else
                <form method="POST" action="{{ route('app.academy.enrol', $course) }}" class="flex-1">@csrf<x-ui.button type="submit" block>Enrol to continue</x-ui.button></form>
            @endif
        </div>
    </div>
</x-layouts.app>
