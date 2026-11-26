<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return view('app.notifications', ['notifications' => $request->user()->notifications()->paginate(20)]);
    }

    public function read(Request $request, string $id)
    {
        $n = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $n->markAsRead();

        $url = $n->data['url'] ?? null;
        // Only follow same-site relative links to avoid open redirects.
        return redirect($url && str_starts_with($url, '/') && ! str_starts_with($url, '//') ? $url : route('app.notifications'));
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->notifications()->whereKey($id)->delete();

        return back();
    }
}
