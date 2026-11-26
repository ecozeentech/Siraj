<?php

namespace App\Services\Community;

use App\Models\Messaging\Conversation;
use App\Models\Messaging\ConversationMember;
use App\Models\Messaging\Message;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MessagingService
{
    public function __construct(private CommunityService $community) {}

    /** @return array{0: Conversation, 1: bool} conversation and whether it is newly created */
    public function open(User $from, User $to): array
    {
        abort_if($from->id === $to->id, 422, 'You cannot message yourself.');
        abort_if($to->status !== 'active', 404);
        abort_if($this->community->isRestricted($from, 'messaging'), 403, 'Your messaging privileges are restricted.');
        abort_if($this->community->hiddenAuthorIds($from)->contains($to->id), 403, 'You cannot message this user.');

        $key = min($from->id, $to->id).':'.max($from->id, $to->id);
        if ($existing = Conversation::query()->where('direct_key', $key)->first()) {
            return [$existing, false];
        }

        $privacy = $to->profile?->message_privacy ?? 'followers';
        abort_if($privacy === 'nobody', 403, 'This user is not accepting new messages.');
        $direct = $privacy === 'everyone' || DB::table('follows')->where(['follower_id' => $to->id, 'followed_id' => $from->id])->exists();

        $conversation = DB::transaction(function () use ($from, $to, $key, $direct) {
            $c = Conversation::query()->create(['direct_key' => $key, 'status' => $direct ? 'active' : 'pending', 'initiated_by' => $from->id]);
            foreach ([$from, $to] as $u) {
                ConversationMember::query()->create(['conversation_id' => $c->id, 'user_id' => $u->id, 'last_read_at' => $u->id === $from->id ? now() : null]);
            }

            return $c;
        });

        return [$conversation, true];
    }

    public function send(User $from, Conversation $conversation, ?string $body, ?UploadedFile $file = null): Message
    {
        abort_if($this->community->isRestricted($from, 'messaging'), 403, 'Your messaging privileges are restricted.');
        $other = $this->otherMember($conversation, $from);
        abort_if($this->community->hiddenAuthorIds($from)->contains($other->id), 403, 'You can no longer message this user.');
        abort_if($conversation->status === 'declined', 403, 'This conversation request was declined.');
        if ($conversation->status === 'pending' && $conversation->initiated_by === $from->id) {
            abort_if($conversation->messages()->where('sender_id', $from->id)->exists(), 429, 'Wait for them to accept your request before sending more messages.');
        }
        abort_if($conversation->status === 'pending' && $conversation->initiated_by !== $from->id, 403, 'Accept this request to reply.');

        $body = trim(strip_tags((string) $body));
        abort_if($body === '' && ! $file, 422, 'Write a message or attach a file.');

        $message = DB::transaction(function () use ($from, $conversation, $body, $file) {
            $m = $conversation->messages()->create(['sender_id' => $from->id, 'body' => $body ?: null]);
            if ($file) {
                $m->attachments()->create([
                    'path' => $file->storeAs('messages/'.$conversation->id, Str::random(40).'.'.($file->guessExtension() ?: 'bin'), 'local'),
                    'name' => Str::limit($file->getClientOriginalName(), 120, ''), 'mime' => $file->getMimeType(), 'size' => $file->getSize(),
                ]);
            }
            $conversation->update(['last_message_at' => now()]);
            ConversationMember::query()->where(['conversation_id' => $conversation->id, 'user_id' => $from->id])->update(['last_read_at' => now()]);

            return $m;
        });

        $member = ConversationMember::query()->where(['conversation_id' => $conversation->id, 'user_id' => $other->id])->first();
        if (! $member?->muted_at && ! $this->recentlyNotified($other, $conversation)) {
            $other->notify(new GeneralNotification($conversation->status === 'pending' ? 'New message request' : 'New message from '.$from->name, $conversation->status === 'pending' ? $from->name.' wants to message you.' : Str::limit($message->body ?: 'Sent an attachment', 100), route('app.messages.show', $conversation, false), 'community', 'message-circle'));
        }

        return $message;
    }

    public function otherMember(Conversation $conversation, User $me): User
    {
        $id = ConversationMember::query()->where('conversation_id', $conversation->id)->where('user_id', '!=', $me->id)->value('user_id');

        return User::query()->withTrashed()->findOrFail($id);
    }

    public function markRead(Conversation $conversation, User $user): void
    {
        ConversationMember::query()->where(['conversation_id' => $conversation->id, 'user_id' => $user->id])->update(['last_read_at' => now()]);
    }

    private function recentlyNotified(User $user, Conversation $conversation): bool
    {
        return $user->notifications()->where('data->url', route('app.messages.show', $conversation, false))->where('read_at', null)->where('created_at', '>', now()->subMinutes(10))->exists();
    }
}
