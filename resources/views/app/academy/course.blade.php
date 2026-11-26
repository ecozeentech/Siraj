<x-layouts.app title="{{ $course->title }}">
    <x-slot:header>
        <header class="relative overflow-hidden lg:rounded-b-[2rem]">
            <x-art.cover :theme="$course->cover_theme" :src="$course->cover_path ? \Illuminate\Support\Facades\Storage::url($course->cover_path) : null" class="h-44 w-full sm:h-56" />
            <a href="{{ route('app.academy.index') }}" class="absolute start-4 grid size-9 place-items-center rounded-full bg-black/30 text-white" style="top: calc(1rem + var(--safe-top))" aria-label="Back"><x-lucide-chevron-left class="size-5" /></a>
        </header>
    </x-slot:header>
    <div class="grid gap-5 py-5 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div><div class="flex flex-wrap items-center gap-2"><span class="badge">{{ ucfirst($course->level) }}</span>@if ($course->category)<span class="badge">{{ $course->category->name }}</span>@endif @if ($course->is_premium)<span class="badge badge-gold">SIRAJ Plus</span>@endif</div><h1 class="mt-2 text-2xl font-bold">{{ $course->title }}</h1>@if ($course->instructor)<p class="text-sm text-muted">By {{ $course->instructor->name }}{{ $course->instructor->title ? ', '.$course->instructor->title : '' }}</p>@endif</div>
            @if ($course->description)<div class="prose-siraj">{!! \Illuminate\Support\Str::markdown($course->description, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>@endif
            <section><h2 class="mb-2 font-bold">Lessons</h2>
                <div class="card divide-y divide-line overflow-hidden">
                    @foreach ($lessons as $i => $l)
                        @php($ok = $enrolment && ! $locked || $l->is_free_preview)
                        <a href="{{ $ok ? route('app.academy.lesson', [$course, $l]) : '#' }}" @class(['list-row', 'opacity-60' => ! $ok]) @unless ($ok) aria-disabled="true" @endunless>
                            <span class="grid size-8 shrink-0 place-items-center rounded-full text-xs font-bold {{ isset($done[$l->id]) ? 'bg-forest-700 text-white' : 'bg-mint text-forest-700' }}">@if (isset($done[$l->id]))<x-lucide-check class="size-4" />@else{{ $i + 1 }}@endif</span>
                            <span class="min-w-0 flex-1"><span class="block truncate font-medium">{{ $l->title }}</span><span class="text-xs text-muted">{{ ucfirst($l->type) }} · {{ $l->duration_minutes }} min @if ($l->is_free_preview)· Free preview @endif</span></span>
                            @unless ($ok)<x-lucide-lock class="size-4 text-muted" />@endunless
                        </a>
                    @endforeach
                </div>
            </section>
            @if ($quizzes->isNotEmpty())
                <section><h2 class="mb-2 font-bold">Quizzes</h2><div class="card divide-y divide-line overflow-hidden">@foreach ($quizzes as $q)<a href="{{ $enrolment ? route('app.academy.quiz', $q) : '#' }}" @class(['list-row', 'opacity-60' => ! $enrolment])><x-lucide-circle-help class="size-5 text-forest-700" /><span class="min-w-0 flex-1"><span class="block font-medium">{{ $q->title }}</span><span class="text-xs text-muted">{{ $q->questions_count }} questions · pass mark {{ $q->pass_mark }}%</span></span>@if (isset($passed[$q->id]))<span class="badge">Passed</span>@endif</a>@endforeach</div></section>
            @endif
        </div>
        <aside class="lg:sticky lg:top-4 lg:self-start"><div class="card space-y-3 p-4">
            @if ($enrolment?->completed_at)
                <div class="text-center"><x-lucide-award class="mx-auto size-10 text-gold-500" /><p class="mt-2 font-bold">Course completed</p></div>
                <x-ui.button :href="route('app.academy.certificate', $enrolment->certificate_code)" variant="gold" block>View certificate</x-ui.button>
            @elseif ($enrolment)
                <p class="font-semibold">Your progress</p><x-ui.progress :value="$enrolment->progress_percent" /><p class="text-sm text-muted">{{ $enrolment->progress_percent }}% complete</p>
                @php($nextLesson = $lessons->first(fn ($l) => ! isset($done[$l->id])) ?? $lessons->first())
                @if ($nextLesson)<x-ui.button :href="route('app.academy.lesson', [$course, $nextLesson])" block>Continue learning</x-ui.button>@endif
            @elseif ($locked)
                <p class="font-semibold">Part of SIRAJ Plus</p><p class="text-sm text-muted">Upgrade to unlock every lesson and quiz in this course.</p><x-ui.button :href="route('app.subscription.index')" variant="gold" block>See plans</x-ui.button>
            @else
                <p class="font-semibold">Free to start</p><form method="POST" action="{{ route('app.academy.enrol', $course) }}">@csrf<x-ui.button type="submit" block>Enrol now</x-ui.button></form>
            @endif
        </div></aside>
    </div>
</x-layouts.app>
