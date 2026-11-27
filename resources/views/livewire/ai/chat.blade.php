<div class="flex flex-1 flex-col" x-data="{ scroll() { this.$nextTick(() => window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' })) } }" @if ($messages->isNotEmpty()) x-init="scroll()" @endif x-on:ai-sent.window="scroll()">
    <div class="flex-1 space-y-4 px-4 py-5 pb-48 lg:pb-32">
        @unless ($connected)
            <x-ui.alert type="warning" title="AI model not connected">An administrator has not connected a language model yet. SIRAJ AI will show matching sources from the Quran and curated library, without generating explanations.</x-ui.alert>
        @endunless

        @if ($messages->isEmpty())
            <div class="flex gap-3">
                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-forest-700 text-white"><x-lucide-sparkles class="size-4" /></span>
                <div class="max-w-[85%] rounded-2xl rounded-tl-sm bg-surface p-4 shadow-card"><p>Assalamu alaikum. Ask me about the Quran, worship and Islamic learning. I answer from SIRAJ's verified library and always show my sources.</p><p class="mt-2 text-xs text-muted">I am not a mufti and cannot give fatwas. For personal rulings, please ask a qualified scholar.</p></div>
            </div>
            <div>
                <p class="mb-2 text-sm font-semibold text-muted">Try asking about:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach (['Prayer times' => 'How are the five daily prayer times determined?', 'Tajweed rules' => 'What does the Quran say about patience?', "Prophets' stories" => 'Who was Prophet Yusuf?', 'Verse 2:153' => 'Explain 2:153'] as $label => $q)
                        <button type="button" wire:click="suggest(@js($q))" class="icon-pop min-h-11 rounded-full border border-line bg-white px-4 py-2 text-sm font-semibold text-ink">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
        @endif

        @foreach ($messages as $m)
            @if ($m->role === 'user')
                <div class="flex justify-end"><p class="max-w-[85%] whitespace-pre-line rounded-2xl rounded-br-md bg-white px-4 py-3 text-ink shadow-card">{{ $m->content }}</p></div>
            @else
                <div class="flex gap-3" wire:key="m-{{ $m->id }}">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-forest-700 text-white"><x-lucide-sparkles class="size-4" /></span>
                    <div class="max-w-[88%] space-y-3">
                        <div class="rounded-2xl rounded-bl-md bg-[#e7f6ee] p-4 text-ink shadow-card dark:bg-forest-800">
                            <p class="whitespace-pre-line leading-7">{{ $m->content }}</p>
                            @if ($m->status !== 'ok')<p class="mt-2 text-xs font-semibold text-gold-600">{{ ['unconfigured' => 'Sources only', 'limited' => 'Daily limit reached', 'error' => 'Temporary error'][$m->status] ?? '' }}</p>@endif
                        </div>
                        @foreach ($m->citations ?? [] as $c)
                            <div class="rounded-2xl border border-line bg-surface p-3 text-sm">
                                <p class="flex items-center gap-2 font-semibold text-forest-700 dark:text-emerald-500"><x-lucide-book-open class="size-4" />{{ $c['title'] }}</p>
                                @if (! empty($c['arabic']))<p class="quran-text mt-2 text-xl leading-[2]" dir="rtl" lang="ar">{{ $c['arabic'] }}</p>@endif
                                @if (! empty($c['text']))<p class="mt-1 text-muted">{{ $c['text'] }}</p>@endif
                                @if (! empty($c['url']))<a href="{{ $c['url'] }}" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-forest-700 dark:text-emerald-500">Learn more <x-lucide-arrow-right class="size-3.5" /></a>@endif
                            </div>
                        @endforeach
                        @if ($m->status === 'ok')
                            <div x-data="{ open: false }" class="text-xs text-muted">
                                <button type="button" class="hover:underline" @click="open = !open">Report this answer</button>
                                <span x-show="open" x-cloak class="ms-2 inline-flex flex-wrap gap-2">
                                    @foreach (['inaccurate' => 'Inaccurate', 'misleading_religious' => 'Religiously misleading', 'harmful' => 'Harmful', 'other' => 'Other'] as $k => $l)
                                        <button type="button" class="rounded-full border border-line px-2.5 py-1 hover:bg-mint" wire:click="report({{ $m->id }}, '{{ $k }}')" @click="open = false">{{ $l }}</button>
                                    @endforeach
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach

        <div wire:loading.flex wire:target="send, suggest" class="gap-3"><span class="grid size-9 place-items-center rounded-full bg-forest-700 text-white"><x-lucide-sparkles class="size-4" /></span><div class="rounded-2xl bg-surface px-4 py-3 text-sm text-muted shadow-card">Looking through sources<span class="animate-pulse">...</span></div></div>
    </div>

    <form wire:submit="send" x-on:submit="$dispatch('ai-sent')" class="fixed inset-x-0 bottom-16 z-40 border-t border-line bg-surface/95 px-4 py-3 backdrop-blur lg:bottom-0 lg:start-64" style="padding-bottom: .75rem">
        <div class="mx-auto max-w-5xl">
            @if ($error)<p class="mb-2 text-sm text-red-600" role="alert">{{ $error }}</p>@endif
            @error('question')<p class="mb-2 text-sm text-red-600" role="alert">{{ $message }}</p>@enderror
            <div class="flex items-center gap-2 rounded-full border border-line bg-surface-2 ps-4 pe-1.5">
                <input type="text" wire:model="question" maxlength="1000" placeholder="Ask a question..." class="min-h-12 flex-1 bg-transparent text-ink outline-none placeholder:text-ink/70" aria-label="Your question" autocomplete="off">
                <button type="submit" class="icon-pop grid size-11 place-items-center rounded-full bg-[#128a49] text-white disabled:opacity-50" wire:loading.attr="disabled" wire:target="send" aria-label="Send"><x-lucide-send class="size-4" /></button>
            </div>
            <p class="mt-1.5 text-center text-[11px] text-muted">{{ $used }} of {{ $limit }} questions used today · Answers may be imperfect. Verify with a scholar.</p>
        </div>
    </form>
</div>
