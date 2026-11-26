<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        return response()->json([
            'id' => '/app',
            'name' => 'SIRAJ Muslim',
            'short_name' => 'SIRAJ',
            'description' => 'Your Faith. Your Journey. Your Ummah. Quran, prayer times, learning, family and community in one app.',
            'start_url' => '/app?source=pwa',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'theme_color' => '#087A55',
            'background_color' => '#FAFBF8',
            'lang' => 'en',
            'dir' => 'ltr',
            'categories' => ['lifestyle', 'education', 'books'],
            'icons' => [
                ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icons/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                ['name' => 'Quran', 'short_name' => 'Quran', 'url' => '/app/quran', 'icons' => [['src' => '/icons/shortcut-quran.png', 'sizes' => '96x96']]],
                ['name' => 'Prayer times', 'short_name' => 'Prayer', 'url' => '/app/worship/prayer-times', 'icons' => [['src' => '/icons/shortcut-quran.png', 'sizes' => '96x96']]],
                ['name' => 'Adhkar', 'short_name' => 'Adhkar', 'url' => '/app/worship/adhkar', 'icons' => [['src' => '/icons/shortcut-quran.png', 'sizes' => '96x96']]],
                ['name' => 'SIRAJ AI', 'short_name' => 'Ask AI', 'url' => '/app/ai', 'icons' => [['src' => '/icons/shortcut-quran.png', 'sizes' => '96x96']]],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json', 'Cache-Control' => 'public, max-age=3600']);
    }

    public function serviceWorker(): Response
    {
        $precache = ['/offline', '/offline/quran', '/offline/adhkar', '/manifest.webmanifest', '/icons/icon-192.png', '/icons/icon-512.png', '/brand/siraj-mark.svg'];
        $manifestPath = public_path('build/manifest.json');
        $buildHash = 'dev';
        if (is_file($manifestPath)) {
            $buildHash = substr(md5_file($manifestPath), 0, 10);
            $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];
            foreach (['resources/js/app.js', 'resources/css/app.css'] as $entry) {
                if (! isset($manifest[$entry])) {
                    continue;
                }
                $precache[] = '/build/'.$manifest[$entry]['file'];
                foreach ($manifest[$entry]['css'] ?? [] as $css) {
                    $precache[] = '/build/'.$css;
                }
                foreach ($manifest[$entry]['imports'] ?? [] as $import) {
                    if (isset($manifest[$import]['file'])) {
                        $precache[] = '/build/'.$manifest[$import]['file'];
                    }
                }
            }
        }

        $body = view('pwa.sw', ['version' => config('siraj.version').'-'.$buildHash, 'precache' => array_values(array_unique($precache))])->render();

        return response($body, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Service-Worker-Allowed' => '/',
        ]);
    }

    public function offline()
    {
        return view('pwa.offline');
    }

    public function offlineQuran()
    {
        return view('pwa.offline-quran');
    }

    public function offlineAdhkar()
    {
        return view('pwa.offline-adhkar', ['categories' => \App\Models\Worship\AdhkarCategory::query()->where('is_published', true)->with(['entries' => fn ($q) => $q->orderBy('sort')->orderBy('id')])->orderBy('sort')->get()]);
    }
}
