<x-layouts.base title="You are offline">
    <main class="mx-auto flex min-h-dvh max-w-md flex-col items-center justify-center gap-6 px-6 text-center">
        <x-brand.mark class="size-16" />
        <div><h1 class="text-2xl font-bold">You're offline</h1><p class="mt-2 text-muted">This page needs a connection. What you've downloaded to this device is still available.</p></div>
        <div class="grid w-full gap-3">
            <a href="{{ route('pwa.offline.quran') }}" class="btn btn-primary min-h-12"><x-lucide-book-open class="size-5" />Read downloaded Quran</a>
            <a href="{{ route('pwa.offline.adhkar') }}" class="btn btn-outline min-h-12"><x-lucide-heart-handshake class="size-5" />Daily adhkar</a>
            <button type="button" class="btn btn-soft min-h-12" onclick="location.reload()"><x-lucide-refresh-cw class="size-5" />Try again</button>
        </div>
        <p class="text-xs text-muted">Actions you take offline (bookmarks, prayer logs, tasbih) are saved on this device and sync automatically when you're back online.</p>
    </main>
</x-layouts.base>
