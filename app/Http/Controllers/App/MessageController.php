<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Messaging\Conversation;
use App\Models\Messaging\ConversationMember;
use App\Models\Messaging\Message;
use App\Models\Messaging\MessageAttachment;
use App\Models\User;
use App\Services\Community\CommunityService;
use App\Services\Community\MessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    public function __construct(private MessagingService $messaging, private CommunityService $community) {}

    public function index(Request $request)
    {
        $me = $request->user();
        $hidden = $this->community->hiddenAuthorIds($me);
        $memberships = ConversationMember::query()->where('user_id', $me->id)->with('conversation')->get()->filter(fn ($m) => $m->conversation && $m->conversation->status !== 'declined' || ($m->conversation && $m->conversation->initiated_by === $me->id));
        $rows = $memberships->map(function ($m) use ($me, $hidden) {
            $c = $m->conversation;
            $other = $this->messaging->otherMember($c, $me);
            $last = $c->messages()->latest('id')->first();

            return ['c' => $c, 'other' => $other, 'last' => $last, 'unread' => $c->messages()->where('sender_id', '!=', $me->id)->when($m->last_read_at, fn ($q) => $q->where('created_at', '>', $m->last_read_at))->count(),
                'hidden' => $hidden->contains($other->id), 'muted' => (bool) $m->muted_at, 'request' => $c->status === 'pending' && $c->initiated_by !== $me->id];
        })->filter(fn ($r) => ! $r['hidden'])->sortByDesc(fn ($r) => $r['c']->last_message_at ?? $r['c']->created_at)->values();

        return view('app.messages.index', ['rows' => $rows->where('request', false)->values(), 'requests' => $rows->where('request', true)->values()]);
    }

    public function start(Request $request)
    {
        $data = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id'], 'body' => ['nullable', 'string', 'max:2000']]);
        [$conversation] = $this->messaging->open($request->user(), User::query()->findOrFail($data['user_id']));
        if (filled($data['body'] ?? null)) {
            $this->messaging->send($request->user(), $conversation, $data['body']);
        }

        return redirect()->route('app.messages.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $me = $request->user();
        $other = $this->messaging->otherMember($conversation, $me);
        $this->messaging->markRead($conversation, $me);
        $messages = $conversation->messages()->with('attachments')->latest('id')->limit(100)->get()->reverse()->values();
        $blocked = $this->community->hiddenAuthorIds($me)->contains($other->id);

        return view('app.messages.show', [
            'conversation' => $conversation, 'other' => $other, 'messages' => $messages, 'blocked' => $blocked,
            'canSend' => ! $blocked && $conversation->status !== 'declined' && ! ($conversation->status === 'pending' && $conversation->initiated_by !== $me->id) && ! ($conversation->status === 'pending' && $messages->contains('sender_id', $me->id)),
            'isRequest' => $conversation->status === 'pending' && $conversation->initiated_by !== $me->id,
            'muted' => (bool) ConversationMember::query()->where(['conversation_id' => $conversation->id, 'user_id' => $me->id])->value('muted_at'),
        ]);
    }

    public function send(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $data = $request->validate(['body' => ['nullable', 'string', 'max:2000'], 'file' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp,pdf']]);
        $this->messaging->send($request->user(), $conversation, $data['body'] ?? null, $request->file('file'));

        return redirect()->route('app.messages.show', $conversation);
    }

    public function respond(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $data = $request->validate(['decision' => ['required', Rule::in(['accept', 'decline'])]]);
        abort_unless($conversation->status === 'pending' && $conversation->initiated_by !== $request->user()->id, 422);
        $conversation->update(['status' => $data['decision'] === 'accept' ? 'active' : 'declined']);

        return $data['decision'] === 'accept' ? redirect()->route('app.messages.show', $conversation)->with('status', 'Request accepted.') : redirect()->route('app.messages.index')->with('status', 'Request declined.');
    }

    public function mute(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);
        $m = ConversationMember::query()->where(['conversation_id' => $conversation->id, 'user_id' => $request->user()->id])->firstOrFail();
        $m->update(['muted_at' => $m->muted_at ? null : now()]);

        return back()->with('status', $m->muted_at ? 'Notifications muted.' : 'Notifications on.');
    }

    public function report(Request $request, Message $message)
    {
        $this->authorize('view', $message->conversation);
        abort_if($message->sender_id === $request->user()->id, 422);
        $data = $request->validate(['reason' => ['required', Rule::in(['spam', 'harassment', 'hate', 'misinformation', 'inappropriate', 'impersonation', 'other'])], 'details' => ['nullable', 'string', 'max:1000']]);
        \App\Models\Community\ContentReport::query()->firstOrCreate(
            ['reporter_id' => $request->user()->id, 'reportable_type' => 'message', 'reportable_id' => $message->id, 'status' => 'open'],
            ['reported_user_id' => $message->sender_id, 'reason' => $data['reason'], 'details' => $data['details'] ?? null],
        );

        return back()->with('status', 'Report sent to our moderators.');
    }

    public function attachment(Request $request, MessageAttachment $attachment)
    {
        $this->authorize('view', $attachment->message->conversation);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->response($attachment->path, $attachment->name, ['X-Content-Type-Options' => 'nosniff', 'Content-Disposition' => 'inline; filename="'.addslashes($attachment->name).'"']);
    }
}
