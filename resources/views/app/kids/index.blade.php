<x-layouts.app title="Kids Deen World">
    <x-slot:header><x-ui.page-header title="Kids Deen World" subtitle="A safe space to play, learn and grow" /></x-slot:header>
    <div class="space-y-5 py-5">
        <div class="overflow-hidden rounded-3xl bg-gradient-to-b from-sky-200 to-[#e8f7de] p-5"><x-art.kids-hero class="mx-auto h-40" /><p class="mt-2 text-center font-display text-2xl font-bold"><span class="text-gold-500">Kids</span> <span class="text-sky-600">Deen World</span></p><p class="text-center text-sm text-forest-800">Play · Learn · Grow</p></div>
        @if ($children->isEmpty())
            <x-ui.empty icon="baby" title="Add your first child profile" text="Each child gets their own space with screen-time limits you control."><x-ui.button :href="route('app.family.index')">Create a child profile</x-ui.button></x-ui.empty>
        @else
            <h2 class="font-bold">Who is playing?</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach ($children as $c)
                    <form method="POST" action="{{ route('app.family.enter', $c) }}">@csrf<button class="card flex w-full flex-col items-center gap-2 p-5 transition hover:shadow-md active:scale-[0.98]"><span class="grid size-16 place-items-center rounded-full bg-gold-100 text-3xl">{{ $c->avatar === 'girl' ? '🧕' : '👦' }}</span><span class="font-bold">{{ $c->name }}</span><span class="text-xs text-muted">Age {{ $c->age() }}</span></button></form>
                @endforeach
            </div>
            <x-ui.button :href="route('app.family.index')" variant="outline" icon="shield-check">Parent controls</x-ui.button>
        @endif
    </div>
</x-layouts.app>
