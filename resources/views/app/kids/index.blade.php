<x-layouts.app title="Kids Deen World" headerless flush>
    <div class="mx-auto max-w-md bg-[#f4fbf6] px-4 pb-6 pt-3 dark:bg-transparent">
        <a href="{{ route('app.home') }}" data-page-back class="icon-pop grid size-11 place-items-center rounded-full hover:bg-white" aria-label="{{ __('Back') }}"><x-lucide-chevron-left class="size-5" /></a>
        <div class="mt-1 text-center">
            <h1 class="font-display text-4xl font-bold leading-none"><span class="text-[#f0b429]">Kids</span> <span class="text-[#2f9ed8]">Deen</span> <span class="text-[#3aaa5a]">World</span></h1>
            <p class="mt-2 text-sm font-semibold text-ink">Play · Learn · Grow</p>
        </div>
        <video class="mx-auto mt-4 w-full max-w-sm overflow-hidden rounded-[1.6rem] bg-white shadow-card" controls playsinline muted loop autoplay poster="{{ asset('images/dash-greet-mosque.png') }}" data-motion-video>
            <source src="{{ asset('videos/kids-deen-world.mp4') }}" type="video/mp4">
        </video>
        <div class="mt-4 text-center">
            @if ($course && $children->isNotEmpty())
                <a href="{{ route('app.family.index') }}" class="icon-pop inline-flex min-h-12 items-center rounded-full bg-[#f0b429] px-8 text-base font-bold text-[#3d3414]">Start Learning</a>
            @else
                <a href="{{ route('app.family.index') }}" class="icon-pop inline-flex min-h-12 items-center rounded-full bg-[#f0b429] px-8 text-base font-bold text-[#3d3414]">Start Learning</a>
            @endif
        </div>
        <div class="mt-6 flex items-center justify-between">
            <h2 class="text-lg font-bold">Fun Games</h2>
            <a href="{{ route('app.family.index') }}" class="text-sm font-semibold text-[#128a49]">See All</a>
        </div>
        <div class="mt-3 grid grid-cols-3 gap-3">
            @forelse ($games as $game)
                @php($icon = in_array($game->icon, ['puzzle', 'book-open', 'heart', 'gamepad-2', 'star', 'sparkles'], true) ? $game->icon : 'puzzle')
                <a href="{{ route('app.family.index') }}" class="icon-pop card flex flex-col items-center gap-2 p-3 text-center">
                    <span class="grid size-12 place-items-center rounded-2xl bg-mint text-[#128a49]"><x-dynamic-component :component="'lucide-'.$icon" class="size-6" /></span>
                    <span class="text-xs font-bold leading-tight">{{ $game->title }}</span>
                </a>
            @empty
                @foreach (['Quran Match' => 'book-open', 'Arabic Puzzle' => 'puzzle', 'Good Deeds' => 'heart'] as $label => $icon)
                    <a href="{{ route('app.family.index') }}" class="icon-pop card flex flex-col items-center gap-2 p-3 text-center">
                        <span class="grid size-12 place-items-center rounded-2xl bg-mint text-[#128a49]"><x-dynamic-component :component="'lucide-'.$icon" class="size-6" /></span>
                        <span class="text-xs font-bold leading-tight">{{ $label }}</span>
                    </a>
                @endforeach
            @endforelse
        </div>
        @if ($children->isNotEmpty())
            <h2 class="mt-6 font-bold">Who is playing?</h2>
            <div class="mt-3 grid grid-cols-2 gap-3">
                @foreach ($children as $c)
                    <form method="POST" action="{{ route('app.family.enter', $c) }}">@csrf<button class="icon-pop card flex w-full flex-col items-center gap-2 p-4"><span class="text-3xl">{{ $c->avatar === 'girl' ? '🧕' : '👦' }}</span><span class="font-bold">{{ $c->name }}</span></button></form>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
