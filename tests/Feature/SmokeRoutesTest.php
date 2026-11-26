<?php

namespace Tests\Feature;

use App\Models\Academy\Course;
use App\Models\Community\Group;
use App\Models\Community\Post;
use App\Models\Directory\Business;
use App\Models\Directory\Mosque;
use App\Models\Events\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Every authenticated page must render for a normal seeded member. */
class SmokeRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_pages_render(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'ahmed@example.com')->firstOrFail();
        $this->actingAs($user);

        $paths = ['/app', '/app/more', '/app/about', '/app/notifications', '/app/profile', '/app/profile/dashboard',
            '/app/settings', '/app/settings/account', '/app/settings/password', '/app/settings/security', '/app/settings/notifications', '/app/settings/appearance',
            '/app/settings/quran', '/app/settings/privacy', '/app/settings/delete', '/app/support',
            '/app/quran', '/app/quran?tab=juz', '/app/quran/surah/1', '/app/quran/surah/2?ayah=255', '/app/quran/surah/18?mode=continuous', '/app/quran/juz/1', '/app/quran/data/1', '/app/quran/search?q=patience', '/app/quran/bookmarks', '/app/quran/goals', '/app/quran/memorisation', '/app/quran/offline',
            '/app/worship', '/app/worship/prayer-times', '/app/worship/timetable', '/app/worship/settings', '/app/worship/qibla', '/app/worship/tracker', '/app/worship/adhkar', '/app/worship/adhkar/favorites', '/app/worship/tasbih', '/app/worship/tajweed',
            '/app/zakat', '/app/hajj-umrah', '/app/academy', '/app/family', '/app/kids',
            '/app/ai', '/app/community', '/app/community?tab=following', '/app/community?tab=popular', '/app/community/people', '/app/community/groups',
            '/app/messages', '/app/events', '/app/events/mine', '/app/directory', '/app/directory?tab=businesses', '/app/directory?tab=halal', '/app/directory/submit', '/app/directory?lat=6.5&lng=3.3',
            '/app/wallet', '/app/subscription', '/offline', '/offline/quran', '/offline/adhkar', '/manifest.webmanifest', '/sw.js',
        ];
        if ($c = Course::query()->first()) {
            $paths[] = '/app/academy/'.$c->slug;
        }
        if ($e = Event::query()->published()->first()) {
            $paths[] = '/app/events/'.$e->slug;
        }
        if ($m = Mosque::query()->where('status', 'published')->first()) {
            $paths[] = '/app/directory/mosques/'.$m->slug;
        }
        if ($b = Business::query()->where('status', 'published')->first()) {
            $paths[] = '/app/directory/businesses/'.$b->slug;
        }
        if ($g = Group::query()->first()) {
            $paths[] = '/app/community/groups/'.$g->slug;
        }
        if ($p = Post::query()->where('status', 'published')->where('visibility', 'public')->whereNull('group_id')->first()) {
            $paths[] = '/app/community/posts/'.$p->id;
            $paths[] = '/app/community/people/'.$p->user_id;
        }
        foreach (\App\Models\Worship\AdhkarCategory::query()->limit(2)->get() as $cat) {
            $paths[] = '/app/worship/adhkar/'.$cat->slug;
        }
        foreach (\App\Models\Worship\TajweedResource::query()->limit(1)->get() as $r) {
            $paths[] = '/app/worship/tajweed/'.$r->slug;
        }
        foreach (\App\Models\Worship\Guide::query()->limit(2)->get() as $gd) {
            $paths[] = '/app/hajj-umrah/'.$gd->slug;
        }

        $failures = [];
        foreach ($paths as $path) {
            $res = $this->get($path);
            if ($res->status() !== 200) {
                $failures[] = $path.' => '.$res->status().($res->exception ? ' '.get_class($res->exception).': '.substr($res->exception->getMessage(), 0, 200) : '');
            }
        }
        $this->assertSame([], $failures, implode("\n", $failures));
    }
}
