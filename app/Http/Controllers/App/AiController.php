<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Ai\AiConversation;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function index(Request $request)
    {
        return view('app.ai', ['conversation' => null, 'history' => $this->history($request)]);
    }

    public function show(Request $request, AiConversation $conversation)
    {
        $this->authorize('view', $conversation);

        return view('app.ai', ['conversation' => $conversation, 'history' => $this->history($request)]);
    }

    public function destroy(Request $request, AiConversation $conversation)
    {
        $this->authorize('delete', $conversation);
        $conversation->delete();

        return redirect()->route('app.ai.index')->with('status', 'Conversation deleted.');
    }

    private function history(Request $request)
    {
        return AiConversation::query()->where('user_id', $request->user()->id)->latest('updated_at')->limit(20)->get();
    }
}
