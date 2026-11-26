<x-layouts.admin :title="$meta[0]">
    <p class="text-sm text-muted">{{ $meta[1] }}</p>
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($resources as $key => $resource)
            @if (auth('admin')->user()->hasPermission($resource['permission']))
                <a href="{{ route('admin.'.$module.'.'.$key.'.index') }}" class="rounded-2xl border border-line bg-white p-4 transition hover:border-forest-700"><p class="font-bold">{{ $resource['label'] }}</p><p class="mt-1 text-sm text-muted">{{ number_format($counts[$key]) }} records</p></a>
            @endif
        @endforeach
    </div>
    @includeWhen($module === 'quran', 'admin.modules.quran')
    @includeWhen($module === 'events', 'admin.modules.events')
    @includeWhen($module === 'directories', 'admin.modules.directories')
</x-layouts.admin>
