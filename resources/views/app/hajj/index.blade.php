<x-layouts.app title="Hajj & Umrah">
    <x-slot:header><x-ui.page-header title="Hajj & Umrah" subtitle="Step-by-step guides and checklists" :back="route('app.worship.index')" /></x-slot:header>
    <div class="grid gap-3 py-5 sm:grid-cols-2">
        @forelse ($guides as $g)
            <a href="{{ route('app.hajj.show', $g->slug) }}" class="card flex gap-4 p-4"><x-art.cover :theme="$g->type === 'hajj' ? 'dawn' : 'mosque_green'" class="size-20 shrink-0 rounded-xl" /><span class="min-w-0 flex-1"><span class="badge">{{ ucfirst($g->type) }}</span><span class="mt-1 block font-semibold">{{ $g->title }}</span><span class="block text-sm text-muted">{{ $g->summary }}</span><span class="mt-1 block text-xs text-muted">{{ $g->steps_count }} steps · {{ $g->checklist_items_count }} checklist items @if (isset($saved[$g->id]))· Saved @endif</span></span></a>
        @empty
            <x-ui.empty icon="compass" title="Guides are on the way" />
        @endforelse
    </div>
    <x-ui.alert type="info" title="A note on accuracy">These guides summarise the main rites and supplications. Rulings can differ between schools of thought and your situation; please follow your group's scholar or Hajj mission guidance.</x-ui.alert>
</x-layouts.app>
