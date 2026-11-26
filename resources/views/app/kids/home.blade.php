@php
    $colors = ['green' => 'bg-emerald-100 text-forest-700', 'gold' => 'bg-gold-100 text-gold-600', 'blue' => 'bg-sky-100 text-sky-700', 'pink' => 'bg-pink-100 text-pink-600', 'purple' => 'bg-violet-100 text-violet-700'];
@endphp
<x-layouts.kids title="Home" :child="$child">
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-b from-sky-200 to-[#e8f7de] p-5 text-center">
        <x-art.kids-hero class="mx-auto h-44" />
        <p class="mt-2 text-lg font-bold text-forest-900">Assalamu alaikum, {{ $child->name }}!</p>
        <div class="mt-2 flex items-center justify-center gap-3 text-sm font-semibold"><span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1"><x-lucide-star class="size-4 text-gold-500" fill="#e4b960" />{{ $stars }} stars</span><span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1"><x-lucide-clock class="size-4 text-sky-600" />{{ $remaining }} min left today</span></div>
        @if ($courses->isNotEmpty())<a href="{{ route('app.kids.course', $courses->first()) }}" class="btn btn-gold mt-4 min-h-14 w-full max-w-xs text-lg">Start Learning</a>@endif
    </section>
    @if ($courses->isNotEmpty())
        <section class="mt-6"><h2 class="text-xl font-bold">Learn</h2>
            <div class="mt-3 grid grid-cols-2 gap-3">@foreach ($courses as $c)<a href="{{ route('app.kids.course', $c) }}" class="rounded-3xl bg-white p-4 shadow-card transition active:scale-[0.97]"><span class="grid size-14 place-items-center rounded-2xl {{ $colors[$c->color] ?? $colors['green'] }}"><x-dynamic-component :component="'lucide-'.$c->icon" class="size-7" /></span><span class="mt-3 block text-base font-bold leading-tight">{{ $c->title }}</span><span class="text-xs text-muted">{{ $c->lessons_count }} lessons</span></a>@endforeach</div></section>
    @endif
    @if ($games->isNotEmpty())
        <section class="mt-6"><h2 class="text-xl font-bold">Fun Games</h2>
            <div class="mt-3 grid grid-cols-3 gap-3">@foreach ($games as $g)<a href="{{ route('app.kids.game', $g) }}" class="flex flex-col items-center gap-2 rounded-3xl bg-white p-3 text-center shadow-card transition active:scale-[0.97]"><span class="grid size-14 place-items-center rounded-2xl {{ $colors[$g->color] ?? $colors['gold'] }}"><x-dynamic-component :component="'lucide-'.$g->icon" class="size-7" /></span><span class="text-sm font-bold leading-tight">{{ $g->title }}</span></a>@endforeach</div></section>
    @endif
    @if ($stories->isNotEmpty())
        <section class="mt-6"><h2 class="text-xl font-bold">Stories</h2>
            <div class="mt-3 space-y-3">@foreach ($stories as $s)<a href="{{ route('app.kids.story', $s) }}" class="flex items-center gap-3 rounded-3xl bg-white p-3 shadow-card active:scale-[0.98]"><x-art.cover :theme="$s->cover_theme" class="size-20 shrink-0 rounded-2xl" /><span class="min-w-0 flex-1"><span class="block text-base font-bold">{{ $s->title }}</span><span class="text-sm text-muted">{{ $s->reference }}</span></span></a>@endforeach</div></section>
    @endif
    @if ($courses->isEmpty() && $games->isEmpty() && $stories->isEmpty())<div class="mt-6 rounded-3xl bg-white p-6 text-center shadow-card"><p class="font-bold">Nothing to show yet</p><p class="text-sm text-muted">Ask your parent to check the Kids settings.</p></div>@endif
</x-layouts.kids>
