<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\System\PushSubscription;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function store(Request $request)
    {
        $d = $request->validate(['endpoint' => ['required', 'url', 'max:2000'], 'keys.p256dh' => ['required', 'string', 'max:255'], 'keys.auth' => ['required', 'string', 'max:255'], 'contentEncoding' => ['nullable', 'in:aesgcm,aes128gcm']]);
        PushSubscription::query()->updateOrCreate(
            ['endpoint_hash' => hash('sha256', $d['endpoint'])],
            ['user_id' => $request->user()->id, 'endpoint' => $d['endpoint'], 'public_key' => $d['keys']['p256dh'], 'auth_token' => $d['keys']['auth'], 'content_encoding' => $d['contentEncoding'] ?? 'aesgcm'],
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request)
    {
        $d = $request->validate(['endpoint' => ['required', 'url']]);
        PushSubscription::query()->where('user_id', $request->user()->id)->where('endpoint_hash', hash('sha256', $d['endpoint']))->delete();

        return response()->json(['ok' => true]);
    }
}
