<?php

namespace Database\Seeders;

use App\Models\Community\Comment;
use App\Models\Community\Group;
use App\Models\Community\GroupMember;
use App\Models\Community\Post;
use App\Models\Directory\Mosque;
use App\Models\Events\Event;
use App\Models\Events\EventCategory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Development/demo data ONLY. Never run in production (guarded in DatabaseSeeder).
 * Directory entries here are unverified demo listings; events are sample events.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        foreach (array_keys(\App\Support\Permissions::ROLES) as $slug) {
            $user = User::query()->firstOrCreate(['email' => "{$slug}@sirajmuslim.test"], [
                'name' => Str::headline($slug), 'password' => $password, 'email_verified_at' => now(), 'onboarded_at' => now(),
            ]);
            $user->forceFill(['is_staff' => true])->save();
            $user->roles()->syncWithoutDetaching(Role::query()->where('slug', $slug)->pluck('id'));
        }

        $make = fn (string $name, string $email, string $username, string $city) => tap(User::query()->firstOrCreate(['email' => $email], [
            'name' => $name, 'password' => $password, 'email_verified_at' => now(), 'onboarded_at' => now(),
        ]), fn (User $u) => $u->profile()->updateOrCreate([], ['username' => $username, 'city' => $city, 'country' => 'Nigeria']));

        $ahmed = $make('Ahmed Mohammed', 'ahmed@example.com', 'ahmed', 'Abuja');
        $photo = public_path('images/demo-ahmed.jpg');
        if (is_file($photo)) {
            Storage::disk('public')->put('avatars/demo-ahmed.jpg', file_get_contents($photo));
            $ahmed->forceFill(['avatar_path' => 'avatars/demo-ahmed.jpg'])->save();
        }
        $amina = $make('Amina Yusuf', 'amina@example.com', 'amina', 'Lagos');
        $reminders = $make('Islamic Reminders', 'reminders@example.com', 'islamicreminders', 'Kano');

        foreach (User::query()->get() as $u) {
            app(\App\Services\UserProvisioner::class)->provision($u);
        }

        if (! Post::query()->exists()) {
            $p1 = Post::query()->create(['user_id' => $amina->id, 'body' => 'May Allah accept our fast and righteous deeds. Ameen', 'location_label' => 'Lagos, Nigeria']);
            Post::query()->create(['user_id' => $reminders->id, 'body' => 'The key to happiness is closeness to Allah.', 'location_label' => 'Kano, Nigeria']);
            Comment::query()->create(['post_id' => $p1->id, 'user_id' => $ahmed->id, 'body' => 'Ameen, ya Rabb.']);
            $p1->update(['comments_count' => 1]);
        }

        if (! Group::query()->exists()) {
            $g = Group::query()->create(['owner_id' => $amina->id, 'name' => 'Quran Circle Lagos', 'slug' => 'quran-circle-lagos', 'description' => 'Weekly reading and reflection group.', 'privacy' => 'public', 'members_count' => 1]);
            GroupMember::query()->create(['group_id' => $g->id, 'user_id' => $amina->id, 'role' => 'owner', 'status' => 'approved']);
        }

        if (! Event::query()->exists()) {
            $cat = fn (string $s) => EventCategory::query()->where('slug', $s)->value('id');
            $sat = now('Africa/Lagos')->next('Saturday')->setTime(17, 0);
            Event::query()->create(['event_category_id' => $cat('lectures'), 'title' => 'Ramadan Lecture Series', 'slug' => 'ramadan-lecture-series', 'summary' => 'An evening lecture series on character and worship.',
                'description' => 'Join us for an evening of reflection. Open to brothers and sisters. Sample event for demonstration.', 'starts_at' => $sat->clone()->utc(), 'ends_at' => $sat->clone()->addHours(2)->utc(),
                'mode' => 'in_person', 'venue_name' => 'National Mosque', 'address' => 'Central Business District', 'city' => 'Abuja', 'latitude' => 9.0583, 'longitude' => 7.4896, 'capacity' => 500,
                'cover_theme' => 'lanterns', 'organizer_name' => 'SIRAJ Community', 'status' => 'published']);
            $mon = now('Africa/Lagos')->next('Monday')->setTime(9, 0);
            Event::query()->create(['event_category_id' => $cat('eid-celebrations'), 'title' => 'Eid Celebration', 'slug' => 'eid-celebration', 'summary' => 'Community Eid gathering.',
                'starts_at' => $mon->clone()->utc(), 'mode' => 'in_person', 'venue_name' => 'Central Mosque', 'city' => 'Lagos', 'latitude' => 6.4524, 'longitude' => 3.3897,
                'cover_theme' => 'dawn', 'organizer_name' => 'SIRAJ Community', 'status' => 'published']);
            Event::query()->create(['event_category_id' => $cat('education'), 'title' => 'Tajweed Basics — Live Online', 'slug' => 'tajweed-basics-online', 'summary' => 'An online introductory session.',
                'starts_at' => now()->addDays(5)->setTime(18, 0), 'mode' => 'online', 'online_url' => 'https://example.com/meeting', 'cover_theme' => 'quran_open', 'organizer_name' => 'SIRAJ Academy', 'status' => 'published']);
        }

        if (! Mosque::query()->exists()) {
            Mosque::query()->create(['name' => 'National Mosque, Abuja', 'slug' => 'national-mosque-abuja', 'city' => 'Abuja', 'state' => 'FCT', 'latitude' => 9.0583, 'longitude' => 7.4896,
                'description' => 'Demo listing — details unverified.', 'facilities' => ['womens_area', 'parking', 'wudu'], 'status' => 'published']);
            Mosque::query()->create(['name' => 'Lagos Central Mosque', 'slug' => 'lagos-central-mosque', 'city' => 'Lagos', 'state' => 'Lagos', 'latitude' => 6.4524, 'longitude' => 3.3897,
                'description' => 'Demo listing — details unverified.', 'facilities' => ['womens_area', 'wudu'], 'status' => 'published']);
        }
    }
}
