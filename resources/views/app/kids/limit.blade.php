<x-layouts.kids title="Time is up" :child="$child">
    <div class="mt-10 rounded-3xl bg-white p-8 text-center shadow-card"><span class="mx-auto grid size-20 place-items-center rounded-full bg-gold-100"><x-lucide-moon-star class="size-10 text-gold-500" /></span><h1 class="mt-4 text-2xl font-bold">That's enough for today!</h1><p class="mt-2 text-muted">Great learning, {{ $child->name }}. Come back tomorrow, in sha Allah. Maybe now is a good time to help at home or read a book.</p></div>
</x-layouts.kids>
