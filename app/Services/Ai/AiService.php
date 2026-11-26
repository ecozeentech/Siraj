<?php

namespace App\Services\Ai;

use App\Models\Ai\AiConversation;
use App\Models\Ai\AiMessage;
use App\Models\Ai\AiModel;
use App\Models\Ai\AiProvider;
use App\Models\Ai\AiSource;
use App\Models\Ai\AiUsageLog;
use App\Models\Quran\Ayah;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * SIRAJ AI: a study companion grounded in sources stored in our database.
 *
 *  - Retrieval: Quran ayahs (verbatim from the verified tables) matched by reference or by translation keywords, plus
 *    admin-curated AiSource records. The model is only ever *shown* these sources and told to cite them by id.
 *  - Output guard: citations are rebuilt from the retrieved sources only; any Quran reference in the answer that was not
 *    retrieved is stripped, and mentions of hadith collections not present in the sources are flagged to the reader.
 *  - If no provider is configured the user still gets the retrieved sources with an honest "not connected" note.
 *  - SIRAJ AI never issues fatwas; the system prompt and UI say so.
 */
class AiService
{
    private const STOP = ['the', 'a', 'an', 'is', 'are', 'was', 'were', 'what', 'who', 'how', 'why', 'when', 'where', 'does', 'do', 'did', 'of', 'in', 'on', 'to', 'and', 'or', 'for', 'about', 'me', 'my', 'i', 'you', 'it', 'this', 'that', 'be', 'can', 'tell', 'islam', 'islamic', 'meaning', 'say', 'says', 'quran', 'allah', 'with', 'from', 'as', 'by', 'at', 'there', 'any', 'explain'];

    private const HADITH_BOOKS = '/\b(bukhari|sahih muslim|sahih al-muslim|tirmidhi|abu dawud|abu dawood|nasa\'?i|ibn majah|muwatta|musnad ahmad|riyad(?:h)? as-?salihin|hadith)\b/i';

    public function ask(User $user, AiConversation $conversation, string $question): AiMessage
    {
        $conversation->messages()->create(['role' => 'user', 'content' => $question]);
        if ($conversation->title === 'New conversation') {
            $conversation->update(['title' => Str::limit($question, 60)]);
        }
        $conversation->touch();

        if ($this->limitReached($user)) {
            $limit = (int) $user->entitlement('ai_daily_limit', 5);

            return $conversation->messages()->create(['role' => 'assistant', 'status' => 'limited', 'content' => "You've reached today's limit of {$limit} questions on your plan. Your limit resets tomorrow. SIRAJ Plus includes a higher daily limit."]);
        }

        if ($this->looksLikeCrisis($question)) {
            return $conversation->messages()->create(['role' => 'assistant', 'status' => 'ok', 'content' => "I'm really sorry you're going through this. You matter, and you don't have to carry it alone. Please reach out right now to someone you trust, or contact your local emergency number or a crisis line. If you are in immediate danger, call emergency services.\n\nTalking to a trusted imam, counsellor or family member can also help. Allah says: \"Indeed, with hardship comes ease.\" (Quran 94:6)"]);
        }

        $sources = $this->retrieve($question, $user);
        [$provider, $model] = $this->activeProvider();

        if (! $provider || ! $model) {
            $this->log($user, null, null, 0, 0, 0, 'unconfigured');

            return $conversation->messages()->create([
                'role' => 'assistant', 'status' => 'unconfigured', 'citations' => $this->citations($sources),
                'content' => $sources
                    ? "SIRAJ AI isn't connected to a language model yet, so I can't write an explanation. Here are the most relevant sources I found in SIRAJ's library for your question. Please read them directly."
                    : "SIRAJ AI isn't connected to a language model yet, and I couldn't find a matching source in SIRAJ's library. Try searching the Quran by keyword or reference, or rephrase your question.",
            ]);
        }

        $started = microtime(true);
        try {
            $history = $conversation->messages()->latest('id')->limit(7)->get()->reverse()->values();
            $payload = [
                'model' => $model->model_id, 'temperature' => (float) $model->temperature, 'max_tokens' => (int) $model->max_output_tokens,
                'messages' => array_merge([['role' => 'system', 'content' => $this->systemPrompt($sources)]], $history->slice(0, -1)->map(fn ($m) => ['role' => $m->role, 'content' => Str::limit($m->content, 1500)])->all(), [['role' => 'user', 'content' => $question]]),
            ];
            $res = Http::withToken($provider->api_key)->baseUrl(rtrim($provider->base_url, '/'))->acceptJson()->timeout(45)->post('/chat/completions', $payload);
            if (! $res->successful()) {
                throw new \RuntimeException('Provider responded '.$res->status());
            }
            $text = trim((string) $res->json('choices.0.message.content'));
            if ($text === '') {
                throw new \RuntimeException('Empty response');
            }
            [$clean, $flags] = $this->guard($text, $sources);
            $in = (int) $res->json('usage.prompt_tokens', 0);
            $out = (int) $res->json('usage.completion_tokens', 0);
            $msg = $conversation->messages()->create(['role' => 'assistant', 'status' => 'ok', 'content' => $clean, 'citations' => $this->citations($sources, $text), 'tokens_in' => $in, 'tokens_out' => $out]);
            $cost = ($in * (float) $model->input_cost_per_million + $out * (float) $model->output_cost_per_million) / 1_000_000;
            $this->log($user, $msg, $model, $in, $out, (int) ((microtime(true) - $started) * 1000), 'ok', null, $cost);

            return $msg;
        } catch (\Throwable $e) {
            report($e);
            $this->log($user, null, $model, 0, 0, (int) ((microtime(true) - $started) * 1000), 'error', Str::limit($e->getMessage(), 200));

            return $conversation->messages()->create(['role' => 'assistant', 'status' => 'error', 'citations' => $this->citations($sources), 'content' => "Sorry, I couldn't get an answer right now. Please try again in a moment.".($sources ? ' Meanwhile, here are related sources from the library.' : '')]);
        }
    }

    public function limitReached(User $user): bool
    {
        $limit = (int) $user->entitlement('ai_daily_limit', 5);

        return $this->usedToday($user) >= $limit;
    }

    public function usedToday(User $user): int
    {
        return AiMessage::query()->where('role', 'user')->where('created_at', '>=', now()->startOfDay())
            ->whereIn('ai_conversation_id', AiConversation::query()->where('user_id', $user->id)->select('id'))->count();
    }

    /** @return array{0: ?AiProvider, 1: ?AiModel} */
    public function activeProvider(): array
    {
        $provider = AiProvider::query()->where('is_active', true)->orderByDesc('is_default')->get()->first(fn ($p) => $p->hasCredentials());
        if (! $provider) {
            return [null, null];
        }
        $model = $provider->models()->where('is_active', true)->orderByDesc('is_default')->first();

        return [$provider, $model];
    }

    /** @return array<int, array{id: string, type: string, ref: string, title: string, text: string, arabic: ?string, url: ?string}> */
    public function retrieve(string $question, User $user): array
    {
        $out = [];
        $n = 1;

        if (preg_match_all('/\b(\d{1,3})\s*[:\.]\s*(\d{1,3})\b/', $question, $m, PREG_SET_ORDER)) {
            foreach (array_slice($m, 0, 3) as [, $s, $a]) {
                if ($ayah = Ayah::findByReference("$s:$a")) {
                    $out[] = $this->quranSource($ayah, $user, $n++);
                }
            }
        }

        $keywords = collect(preg_split('/[^a-z0-9\']+/i', Str::lower($question)))->filter(fn ($w) => $w !== '' && mb_strlen($w) > 3 && ! in_array($w, self::STOP, true))->unique()->take(4)->values();
        if ($keywords->isNotEmpty() && count($out) < 4) {
            $trId = $this->translationId($user);
            if ($trId) {
                $rows = DB::table('translations')->where('translation_source_id', $trId)->where(function ($w) use ($keywords) {
                    foreach ($keywords as $k) {
                        $w->orWhere('text', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $k).'%');
                    }
                })->limit(60)->get(['ayah_id', 'text']);
                $scored = $rows->map(fn ($r) => ['ayah_id' => $r->ayah_id, 'score' => $keywords->filter(fn ($k) => str_contains(Str::lower($r->text), $k))->count()])->sortByDesc('score')->take(4 - count($out));
                $have = collect($out)->pluck('ayah_id');
                foreach ($scored as $r) {
                    if ($r['score'] >= min(2, $keywords->count()) && ! $have->contains($r['ayah_id']) && ($ayah = Ayah::query()->with('surah')->find($r['ayah_id']))) {
                        $out[] = $this->quranSource($ayah, $user, $n++);
                    }
                }
            }
        }

        if ($keywords->isNotEmpty()) {
            AiSource::query()->where('is_active', true)->where(function ($w) use ($keywords) {
                foreach ($keywords as $k) {
                    $w->orWhere('title', 'like', "%{$k}%")->orWhere('content', 'like', "%{$k}%");
                }
            })->limit(3)->get()->each(function (AiSource $s) use (&$out, &$n) {
                $out[] = ['id' => 'S'.$n++, 'type' => $s->type, 'ref' => $s->reference ?: $s->title, 'title' => $s->title.($s->is_verified ? '' : ' (unverified)'), 'text' => Str::limit($s->content, 900), 'arabic' => null, 'url' => $s->url];
            });
        }

        return $out;
    }

    private function translationId(User $user): ?int
    {
        return $user->preferences?->translation_source_id ?: DB::table('translation_sources')->orderBy('id')->value('id');
    }

    private function quranSource(Ayah $ayah, User $user, int $n): array
    {
        $trId = $this->translationId($user);
        $text = $trId ? (string) DB::table('translations')->where('translation_source_id', $trId)->where('ayah_id', $ayah->id)->value('text') : '';

        return [
            'id' => 'S'.$n, 'type' => 'quran', 'ref' => $ayah->reference(), 'ayah_id' => $ayah->id, 'title' => 'Quran '.$ayah->reference(),
            'text' => $text, 'arabic' => $ayah->displayText(), 'url' => route('app.quran.surah', ['surah' => $ayah->surah_id, 'ayah' => $ayah->number_in_surah], false),
        ];
    }

    private function systemPrompt(array $sources): string
    {
        $block = collect($sources)->map(fn ($s) => "[{$s['id']}] {$s['title']}: ".($s['text'] ?: '(Arabic text only)'))->implode("\n");

        return "You are SIRAJ AI, a respectful study companion inside a Muslim lifestyle app.\n"
            ."Rules:\n- You are NOT a mufti and must not issue fatwas or personal rulings. For rulings, advise asking a qualified local scholar.\n"
            ."- Base every religious statement on the SOURCES below and cite them inline as [S1], [S2]. Never cite a source that is not listed.\n"
            ."- Never quote or invent Quran verses or hadith yourself. Do not give hadith grades or narrations unless they appear in the sources.\n"
            ."- If the sources do not cover the question, say so plainly and suggest where to learn more. Be concise, kind and neutral; explain differences of opinion respectfully.\n"
            .'- Reply in the language of the question.'
            ."\n\nSOURCES:\n".($block ?: '(no sources found for this question)');
    }

    /** @return array{0: string, 1: array<string>} */
    private function guard(string $text, array $sources): array
    {
        $flags = [];
        $allowedRefs = collect($sources)->where('type', 'quran')->pluck('ref')->all();
        $text = preg_replace_callback('/\b(?:Quran|Qur\'an|Surah|Q)\s*\.?\s*\(?(\d{1,3}):(\d{1,3})\)?/i', function ($m) use ($allowedRefs, &$flags) {
            $ref = (int) $m[1].':'.(int) $m[2];
            if (in_array($ref, $allowedRefs, true)) {
                return $m[0];
            }
            $flags[] = 'quran_ref_removed';

            return '[reference not verified]';
        }, $text);
        $text = preg_replace_callback('/\((\d{1,3}):(\d{1,3})\)/', function ($m) use ($allowedRefs, &$flags) {
            if (in_array((int) $m[1].':'.(int) $m[2], $allowedRefs, true)) {
                return $m[0];
            }
            $flags[] = 'quran_ref_removed';

            return '[reference not verified]';
        }, $text);

        $hadithInSources = collect($sources)->contains(fn ($s) => preg_match(self::HADITH_BOOKS, $s['title'].' '.$s['text']));
        if (! $hadithInSources && preg_match(self::HADITH_BOOKS, $text)) {
            $flags[] = 'hadith_unverified';
            $text .= "\n\nNote: this answer mentions hadith that are not in SIRAJ's verified library. Please verify them with a qualified scholar or a trusted hadith source before relying on them.";
        }

        return [trim($text), $flags];
    }

    private function citations(array $sources, ?string $answer = null): array
    {
        $cited = collect($sources);
        if ($answer !== null && preg_match_all('/\[S(\d+)\]/', $answer, $m) && count($m[1])) {
            $ids = collect($m[1])->map(fn ($i) => 'S'.$i)->unique();
            $cited = $cited->filter(fn ($s) => $ids->contains($s['id']));
        }

        return $cited->map(fn ($s) => collect($s)->only(['id', 'type', 'ref', 'title', 'text', 'arabic', 'url'])->all())->values()->all();
    }

    private function looksLikeCrisis(string $q): bool
    {
        return (bool) preg_match('/\b(kill myself|end my life|suicide|suicidal|want to die|hurt myself|self[- ]harm)\b/i', $q);
    }

    private function log(User $user, ?AiMessage $msg, ?AiModel $model, int $in, int $out, int $ms, string $status, ?string $error = null, float $cost = 0): void
    {
        AiUsageLog::query()->create(['user_id' => $user->id, 'ai_message_id' => $msg?->id, 'ai_model_id' => $model?->id, 'tokens_in' => $in, 'tokens_out' => $out, 'cost_usd' => $cost, 'latency_ms' => $ms, 'status' => $status, 'error' => $error, 'created_at' => now()]);
    }
}
