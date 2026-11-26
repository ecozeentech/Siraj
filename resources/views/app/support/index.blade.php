<x-layouts.app title="Help & Support">
    <x-slot:header><x-ui.page-header title="Help & Support" :back="route('app.profile.show')" /></x-slot:header>
    <div class="mx-auto max-w-2xl space-y-6 py-5">
        <section>
            <h2 class="font-bold">Frequently asked questions</h2>
            <div class="card mt-3 divide-y divide-line overflow-hidden">
                @foreach ([
                    ['How accurate are the prayer times?', 'Times are calculated astronomically from your location with the method you choose in Settings (accurate to within a minute or two of published timetables using the same method). Your local mosque may differ slightly, so you can add minute adjustments per prayer.'],
                    ['Where does the Quran text come from?', 'The Arabic text is imported from a published dataset with its source and checksum recorded, and verified by automated integrity checks. Translations and tafsir show their source and license. SIRAJ never generates Quran text with AI.'],
                    ['Is SIRAJ AI a source of religious rulings?', 'No. SIRAJ AI is a study companion that points to real Quran verses and sources. It does not issue fatwas. For personal rulings please ask a qualified scholar.'],
                    ['Does the app work offline?', 'Yes, partly. Install SIRAJ to your home screen, then download surahs under Settings > Offline Quran. Adhkar and prayer actions made offline sync automatically when you reconnect.'],
                    ['How do I use Kids Deen World safely?', 'Create child profiles under Family Management. While a child profile is active, the rest of the app is locked and leaving requires your account password. You can set daily limits per child.'],
                    ['How is my prayer tracking used?', 'It is private by default and only shared if you turn on "Share prayer activity" in Privacy.'],
                ] as [$q, $a])
                    <details class="group px-4 py-3"><summary class="flex cursor-pointer list-none items-center justify-between font-medium">{{ $q }}<x-lucide-chevron-down class="size-4 transition group-open:rotate-180" /></summary><p class="mt-2 text-sm leading-6 text-muted">{{ $a }}</p></details>
                @endforeach
            </div>
        </section>
        <section>
            <h2 class="font-bold">Contact support</h2>
            <form method="POST" action="{{ route('app.support.store') }}" class="card mt-3 space-y-4 p-4">@csrf
                <x-ui.select name="category" label="Topic" :options="$categories" />
                <x-ui.input name="subject" label="Subject" required />
                <x-ui.textarea name="message" label="How can we help?" rows="5" required />
                <x-ui.button type="submit" block>Send request</x-ui.button>
            </form>
        </section>
        @if ($tickets->isNotEmpty())
            <section>
                <h2 class="font-bold">Your requests</h2>
                <div class="card mt-3 divide-y divide-line overflow-hidden">
                    @foreach ($tickets as $t)
                        <a href="{{ route('app.support.show', $t) }}" class="list-row"><span class="min-w-0 flex-1"><span class="block truncate font-medium">{{ $t->subject }}</span><span class="text-xs text-muted">{{ $t->created_at->diffForHumans() }}</span></span><span class="badge">{{ ucfirst($t->status) }}</span></a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
