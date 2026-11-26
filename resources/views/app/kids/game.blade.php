<x-layouts.kids :title="$game->title" :child="$child" :back="route('app.kids.home')">
    <div x-data="kidsGame({ type: @js($game->type), config: @js($game->config), completeUrl: @js(route('app.kids.game.complete', $game)) })">
        <h1 class="text-2xl font-bold">{{ $game->title }}</h1><p class="text-muted">{{ $game->summary }}</p>
        <div x-show="!done" class="mt-4">
            @if ($game->type === 'match')
                <p class="mb-3 text-sm font-semibold text-sky-700">Find the matching pairs. Moves: <span x-text="moves"></span></p>
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                    <template x-for="(c, i) in cards" :key="c.id"><button type="button" class="grid aspect-square place-items-center rounded-3xl text-2xl font-bold shadow-card transition" :class="isUp(i) ? (c.ar ? 'bg-gold-100 text-forest-900' : 'bg-sky-100 text-sky-800') : 'bg-forest-700 text-white'" @click="flip(i)" :aria-label="isUp(i) ? c.text : 'Hidden card'"><span :class="c.ar ? 'quran-text !leading-none text-5xl' : ''" x-show="isUp(i)" x-text="c.text"></span><x-lucide-sparkles class="size-7" x-show="!isUp(i)" /></button></template>
                </div>
            @else
                <div class="rounded-3xl bg-white p-6 text-center shadow-card" x-show="question">
                    <p class="text-sm font-semibold text-sky-700" x-text="'Question ' + (qIndex + 1) + ' of ' + config.questions.length"></p>
                    <p class="mt-3 text-xl font-bold" x-text="question?.q"></p>
                    <div class="mt-5 grid gap-3">
                        <template x-for="(o, i) in question?.options" :key="i"><button type="button" class="min-h-14 rounded-2xl border-2 px-4 text-lg font-bold transition" :class="picked === null ? 'border-line bg-white hover:bg-surface-2' : (i === question.answer ? 'border-emerald-500 bg-emerald-100' : (i === picked ? 'border-red-400 bg-red-50' : 'border-line opacity-60'))" @click="pick(i)" x-text="o"></button></template>
                    </div>
                </div>
            @endif
        </div>
        <div x-show="done" x-cloak class="mt-6 rounded-3xl bg-white p-8 text-center shadow-card">
            <p class="text-2xl font-bold">Masha Allah!</p>
            <div class="mt-3 flex justify-center gap-1"><template x-for="n in 3" :key="n"><x-lucide-star class="size-12" x-bind:class="n <= stars ? 'text-gold-400' : 'text-line'" x-bind:fill="n <= stars ? 'currentColor' : 'none'" /></template></div>
            <p class="mt-2 text-muted">Score: <span x-text="score"></span>%</p>
            <div class="mt-5 flex gap-3"><button type="button" class="btn btn-outline min-h-12 flex-1" @click="restart()">Play again</button><a href="{{ route('app.kids.home') }}" class="btn btn-gold min-h-12 flex-1">Done</a></div>
        </div>
    </div>
</x-layouts.kids>
