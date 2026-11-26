<x-layouts.app title="Quiz result">
    <x-slot:header><x-ui.page-header title="Quiz result" :subtitle="$quiz->title" :back="route('app.academy.course', $quiz->course)" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-4 py-5">
        <div class="card p-6 text-center"><p class="text-5xl font-bold {{ $attempt->passed ? 'text-forest-700 dark:text-emerald-500' : 'text-red-600' }}">{{ $attempt->score }}%</p><p class="mt-2 font-semibold">{{ $attempt->passed ? 'Masha Allah, you passed!' : 'Not passed this time' }}</p><p class="text-sm text-muted">{{ $attempt->correct_count }} of {{ $attempt->question_count }} correct · pass mark {{ $quiz->pass_mark }}%</p></div>
        @foreach ($questions as $i => $q)
            @php($r = $attempt->responses[$q->id] ?? ['picked' => [], 'correct' => false])
            <div class="card p-4"><p class="flex items-start gap-2 font-semibold"><span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full {{ $r['correct'] ? 'bg-emerald-100 text-forest-700' : 'bg-red-100 text-red-600' }}"><x-dynamic-component :component="'lucide-'.($r['correct'] ? 'check' : 'x')" class="size-3.5" /></span>{{ $q->body }}</p>
                <ul class="mt-2 space-y-1 text-sm">@foreach ($q->answers as $a)<li class="flex items-center gap-2 rounded-lg px-2 py-1 {{ $a->is_correct ? 'bg-emerald-50 font-semibold text-forest-800' : (in_array($a->id, $r['picked']) ? 'bg-red-50 text-red-700' : 'text-muted') }}">{{ $a->body }} @if ($a->is_correct)<span class="text-xs">(correct)</span>@elseif (in_array($a->id, $r['picked']))<span class="text-xs">(your answer)</span>@endif</li>@endforeach</ul>
                @if ($q->explanation)<p class="mt-2 text-sm text-muted">{{ $q->explanation }}</p>@endif
            </div>
        @endforeach
        <div class="flex gap-3"><x-ui.button :href="route('app.academy.quiz', $quiz)" variant="outline" class="flex-1">Try again</x-ui.button><x-ui.button :href="route('app.academy.course', $quiz->course)" class="flex-1">Back to course</x-ui.button></div>
    </div>
</x-layouts.app>
