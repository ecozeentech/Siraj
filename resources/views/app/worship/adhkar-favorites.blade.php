<x-layouts.app title="Favourite adhkar">
    <x-slot:header><x-ui.page-header title="Favourites" :back="route('app.worship.adhkar')" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-3 py-5">
        @forelse ($entries as $e)
            <a href="{{ route('app.worship.adhkar.show', $e->category->slug) }}" class="card block p-4"><span class="text-xs font-semibold text-forest-700 dark:text-emerald-500">{{ $e->category->title() }}</span><span class="quran-text mt-2 block text-2xl" dir="rtl">{{ $e->text_ar }}</span></a>
        @empty
            <x-ui.empty icon="heart" title="No favourites yet" text="Tap the heart on any dhikr to keep it here."><x-ui.button :href="route('app.worship.adhkar')">Browse adhkar</x-ui.button></x-ui.empty>
        @endforelse
    </div>
</x-layouts.app>
