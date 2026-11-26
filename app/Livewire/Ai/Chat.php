<?php

namespace App\Livewire\Ai;

use App\Models\Ai\AiConversation;
use App\Models\Ai\AiMessage;
use App\Models\Ai\AiReport;
use App\Services\Ai\AiService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Chat extends Component
{
    #[Locked]
    public ?int $conversationId = null;

    public string $question = '';

    public ?string $error = null;

    public function mount(?int $conversationId = null): void
    {
        $this->conversationId = $conversationId;
        if ($conversationId) {
            $this->conversation(); // authorises
        }
    }

    private function conversation(): ?AiConversation
    {
        if (! $this->conversationId) {
            return null;
        }

        return AiConversation::query()->where('user_id', auth()->id())->findOrFail($this->conversationId);
    }

    public function suggest(string $text): void
    {
        $this->question = $text;
        $this->send();
    }

    public function send(AiService $ai): void
    {
        $this->error = null;
        $this->validate(['question' => ['required', 'string', 'min:2', 'max:1000']], [], ['question' => 'question']);
        $user = auth()->user();

        $key = 'ai-chat:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->error = 'You are asking very quickly. Please wait a moment.';

            return;
        }
        RateLimiter::hit($key, 60);

        $conversation = $this->conversation() ?? AiConversation::query()->create(['user_id' => $user->id]);
        $this->conversationId = $conversation->id;
        $q = trim($this->question);
        $this->question = '';
        $ai->ask($user, $conversation, $q);
    }

    public function report(int $messageId, string $reason): void
    {
        $message = AiMessage::query()->where('role', 'assistant')->whereIn('ai_conversation_id', AiConversation::query()->where('user_id', auth()->id())->select('id'))->findOrFail($messageId);
        abort_unless(in_array($reason, ['inaccurate', 'harmful', 'misleading_religious', 'other'], true), 422);
        AiReport::query()->firstOrCreate(['ai_message_id' => $message->id, 'user_id' => auth()->id()], ['reason' => $reason]);
        $this->dispatch('toast', message: 'Thank you. Our team will review this answer.');
    }

    public function render(AiService $ai)
    {
        $conversation = $this->conversation();
        $user = auth()->user();

        return view('livewire.ai.chat', [
            'messages' => $conversation ? $conversation->messages()->get() : collect(),
            'used' => $ai->usedToday($user), 'limit' => (int) $user->entitlement('ai_daily_limit', 5),
            'connected' => $ai->activeProvider()[0] !== null,
        ]);
    }
}
