<x-layouts.app :title="$other->name" headerless flush>
    <div class="mx-auto flex min-h-dvh max-w-2xl flex-col" x-data="chat({ url: @js(route('app.messages.show', $conversation)) })">
        <header class="sticky top-0 z-30 flex items-center gap-3 border-b border-line bg-surface/95 px-4 py-3 backdrop-blur" style="padding-top: calc(.75rem + var(--safe-top))">
            <a href="{{ route('app.messages.index') }}" class="grid size-9 place-items-center rounded-full hover:bg-surface-2" aria-label="Back"><x-lucide-chevron-left class="size-5" /></a>
            <a href="{{ route('app.community.profile', $other) }}" class="flex min-w-0 flex-1 items-center gap-3"><x-ui.avatar :user="$other" size="sm" /><span class="truncate font-semibold">{{ $other->name }}</span></a>
            <form method="POST" action="{{ route('app.messages.mute', $conversation) }}">@csrf<button class="grid size-9 place-items-center rounded-full hover:bg-surface-2" aria-label="{{ $muted ? 'Unmute' : 'Mute' }} notifications"><x-dynamic-component :component="$muted ? 'lucide-bell-off' : 'lucide-bell'" class="size-5" /></button></form>
        </header>
        <div id="thread" class="flex-1 space-y-2 px-4 py-4 pb-44 lg:pb-32">
            @foreach ($messages as $m)
                @php($mine = $m->sender_id === auth()->id())
                <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}" x-data>
                    <div class="max-w-[82%] rounded-2xl px-3.5 py-2 {{ $mine ? 'rounded-tr-sm bg-forest-700 text-white' : 'rounded-tl-sm bg-surface shadow-card' }}">
                        @if ($m->body)<p class="whitespace-pre-line break-words">{{ $m->body }}</p>@endif
                        @foreach ($m->attachments as $a)
                            @if (str_starts_with($a->mime, 'image/'))<a href="{{ route('app.messages.attachment', $a) }}" target="_blank" rel="noopener"><img src="{{ route('app.messages.attachment', $a) }}" alt="{{ $a->name }}" class="mt-1 max-h-60 rounded-xl"></a>
                            @else<a href="{{ route('app.messages.attachment', $a) }}" target="_blank" rel="noopener" class="mt-1 flex items-center gap-2 underline"><x-lucide-file-text class="size-4" />{{ $a->name }}</a>@endif
                        @endforeach
                        <p class="mt-0.5 flex items-center justify-end gap-2 text-[10px] {{ $mine ? 'text-white/70' : 'text-muted' }}">{{ $m->created_at->format('H:i') }}
                            @unless ($mine)<button type="button" class="hover:underline" @click="$dispatch('report', { url: @js(route('app.messages.report', $m)), label: 'message' })">Report</button>@endunless</p>
                    </div>
                </div>
            @endforeach
            @if ($messages->isEmpty())<p class="py-10 text-center text-sm text-muted">No messages yet.</p>@endif
        </div>
        <div class="fixed inset-x-0 bottom-16 z-40 border-t border-line bg-surface/95 px-4 py-3 backdrop-blur lg:bottom-0 lg:start-64">
            <div class="mx-auto max-w-2xl">
                @if ($isRequest)
                    <div class="flex items-center gap-2"><p class="flex-1 text-sm">{{ $other->name }} wants to message you.</p>
                        <form method="POST" action="{{ route('app.messages.respond', $conversation) }}">@csrf<input type="hidden" name="decision" value="decline"><x-ui.button type="submit" variant="outline" size="sm">Decline</x-ui.button></form>
                        <form method="POST" action="{{ route('app.messages.respond', $conversation) }}">@csrf<input type="hidden" name="decision" value="accept"><x-ui.button type="submit" size="sm">Accept</x-ui.button></form></div>
                @elseif ($blocked)
                    <p class="text-center text-sm text-muted">You can't message this person.</p>
                @elseif (! $canSend)
                    <p class="text-center text-sm text-muted">{{ $conversation->status === 'declined' ? 'This request was declined.' : 'Your message request is waiting for a reply.' }}</p>
                @else
                    @if ($errors->any())<p class="mb-2 text-sm text-red-600" role="alert">{{ $errors->first() }}</p>@endif
                    <form method="POST" action="{{ route('app.messages.send', $conversation) }}" enctype="multipart/form-data" class="flex items-end gap-2" x-data="{ f: '' }">@csrf
                        <label class="grid size-11 shrink-0 cursor-pointer place-items-center rounded-full bg-surface-2 hover:bg-mint" aria-label="Attach a file"><x-lucide-paperclip class="size-5" /><input type="file" name="file" accept="image/jpeg,image/png,image/webp,application/pdf" class="sr-only" @change="f = $event.target.files[0]?.name || ''"></label>
                        <div class="min-w-0 flex-1"><p x-show="f" x-cloak class="truncate text-xs text-muted" x-text="f"></p><input name="body" maxlength="2000" autocomplete="off" class="field !rounded-full" placeholder="Write a message..." aria-label="Message"></div>
                        <button class="grid size-11 shrink-0 place-items-center rounded-full bg-forest-700 text-white" aria-label="Send"><x-lucide-send class="size-5" /></button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    <x-community.report-sheet />
    <script>document.addEventListener('alpine:init', () => { Alpine.data('chat', (cfg) => ({ init() { window.scrollTo(0, document.body.scrollHeight); this.t = setInterval(() => { if (!document.hidden && navigator.onLine && !document.activeElement?.matches('input[name=body]')) fetch(cfg.url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.text()).then(h => { const d = new DOMParser().parseFromString(h, 'text/html').getElementById('thread'); const cur = document.getElementById('thread'); if (d && cur && d.children.length !== cur.children.length) { cur.innerHTML = d.innerHTML; window.scrollTo(0, document.body.scrollHeight); } }).catch(() => {}); }, 8000); }, destroy() { clearInterval(this.t); } })); });</script>
</x-layouts.app>
