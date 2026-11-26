<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Quran\Reciter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Same-origin audio proxy. The audio CDN doesn't send CORS headers, which prevents storing audio in
 * Cache Storage for offline use; serving it from our origin (and caching it on disk once) solves that
 * and gives us Range request support for seeking.
 */
class QuranAudioController extends Controller
{
    public function __invoke(string $reciter, int $ayah)
    {
        abort_unless($ayah >= 1 && $ayah <= 6236, 404);
        $r = Reciter::query()->where('slug', $reciter)->where('is_active', true)->firstOrFail();
        $path = "audio-cache/{$r->slug}/{$ayah}.mp3";
        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            $url = $r->urlForAyah($ayah);
            abort_unless($url, 404);
            $res = Http::timeout(20)->retry(2, 200, throw: false)->get($url);
            abort_unless($res->successful() && str_contains((string) $res->header('Content-Type'), 'audio'), 502, 'Audio source unavailable.');
            $disk->put($path, $res->body());
        }

        return response()->file($disk->path($path), ['Content-Type' => 'audio/mpeg', 'Cache-Control' => 'public, max-age=604800', 'Accept-Ranges' => 'bytes']);
    }
}
