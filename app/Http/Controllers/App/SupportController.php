<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\System\SupportTicket;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public const CATEGORIES = ['general' => 'General question', 'account' => 'Account & login', 'billing' => 'Billing & subscription', 'content' => 'Content or religious accuracy', 'bug' => 'Report a problem', 'safety' => 'Safety or abuse'];

    public function index(Request $request)
    {
        return view('app.support.index', ['tickets' => SupportTicket::query()->where('user_id', $request->user()->id)->latest()->get(), 'categories' => self::CATEGORIES]);
    }

    public function store(Request $request)
    {
        $d = $request->validate(['subject' => ['required', 'string', 'max:160'], 'category' => ['required', 'in:'.implode(',', array_keys(self::CATEGORIES))], 'message' => ['required', 'string', 'min:10', 'max:5000']]);
        $ticket = SupportTicket::query()->create($d + ['user_id' => $request->user()->id, 'priority' => $d['category'] === 'safety' ? 'high' : 'normal']);

        return redirect()->route('app.support.show', $ticket)->with('status', 'Your request was sent. We will reply here and by notification.');
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);

        return view('app.support.show', ['ticket' => $ticket->load('replies.user')]);
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === $request->user()->id, 404);
        $d = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $ticket->replies()->create(['user_id' => $request->user()->id, 'is_staff' => false, 'body' => $d['body']]);
        $ticket->update(['status' => 'open']);

        return back()->with('status', 'Reply sent.');
    }
}
