<x-layouts.app title="{{ $quiz->title }}">
    <x-slot:header><x-ui.page-header :title="$quiz->title" :subtitle="$course->title.' · pass mark '.$quiz->pass_mark.'%'" :back="route('app.academy.course', $course)" /></x-slot:header>
    <form method="POST" action="{{ route('app.academy.quiz.submit', $quiz) }}" class="mx-auto max-w-2xl space-y-4 py-5">@csrf
        @if ($best !== null)<x-ui.alert type="info">Your best score so far: {{ $best }}%</x-ui.alert>@endif
        @forelse ($questions as $i => $q)
            <fieldset class="card p-4"><legend class="sr-only">Question {{ $i + 1 }}</legend>
                <p class="font-semibold"><span class="me-1 text-forest-700 dark:text-emerald-500">{{ $i + 1 }}.</span>{{ $q->body }}</p>
                @if ($q->type === 'multiple')<p class="text-xs text-muted">Select all that apply.</p>@endif
                <div class="mt-3 space-y-2">
                    @foreach ($q->answers as $a)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-line p-3 has-[:checked]:border-forest-700 has-[:checked]:bg-mint">
                            <input type="{{ $q->type === 'multiple' ? 'checkbox' : 'radio' }}" name="answers[{{ $q->id }}][]" value="{{ $a->id }}" class="size-5 border-line text-forest-700"><span class="text-sm">{{ $a->body }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @empty
            <x-ui.empty icon="circle-help" title="No questions yet" />
        @endforelse
        @if ($questions->isNotEmpty())<x-ui.button type="submit" block>Submit answers</x-ui.button>@endif
    </form>
</x-layouts.app>
