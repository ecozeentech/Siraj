<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Kids\KidsCourse;
use App\Models\Kids\KidsGame;
use App\Models\Kids\KidsLesson;
use App\Models\Kids\KidsProgress;
use App\Models\Kids\KidsStory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KidsController extends Controller
{
    private function child(Request $request)
    {
        return $request->attributes->get('kids_child') ?? abort(redirect()->route('app.kids.index'));
    }

    private function allowed(Request $request, string $section)
    {
        abort_unless(in_array($section, $this->child($request)->controls->allowed_sections ?? [], true), 403, 'This section is turned off by your parent.');
    }

    private function forAge($query, $child)
    {
        return $query->where('age_min', '<=', $child->age())->where('age_max', '>=', $child->age());
    }

    /** Parent-facing entry: choose which child profile to start. */
    public function index(Request $request)
    {
        if ($request->session()->get('kids_child_id')) {
            return redirect()->route('app.kids.home');
        }
        $family = $request->user()->ownedFamilies()->first();

        return view('app.kids.index', [
            'children' => $family ? $family->children()->get() : collect(),
            'games' => KidsGame::query()->where('is_published', true)->orderBy('sort')->limit(6)->get(),
            'course' => KidsCourse::query()->where('is_published', true)->orderBy('sort')->first(),
        ]);
    }

    public function home(Request $request)
    {
        $child = $this->child($request);
        $allowed = $child->controls->allowed_sections ?? [];
        $stars = (int) $child->progress()->sum('stars');
        $usedMin = (int) floor(DB::table('child_screen_time')->where('child_profile_id', $child->id)->where('day', today()->toDateString())->value('seconds') / 60);

        return view('app.kids.home', [
            'child' => $child, 'stars' => $stars, 'allowed' => $allowed,
            'courses' => in_array('courses', $allowed) ? $this->forAge(KidsCourse::query()->where('is_published', true), $child)->orderBy('sort')->withCount('lessons')->get() : collect(),
            'stories' => in_array('stories', $allowed) ? $this->forAge(KidsStory::query()->where('is_published', true), $child)->orderBy('sort')->get() : collect(),
            'games' => in_array('games', $allowed) ? $this->forAge(KidsGame::query()->where('is_published', true), $child)->orderBy('sort')->get() : collect(),
            'remaining' => max(0, $child->controls->daily_limit_minutes - $usedMin),
        ]);
    }

    public function course(Request $request, KidsCourse $course)
    {
        $child = $this->child($request);
        $this->allowed($request, 'courses');
        abort_unless($course->is_published, 404);
        $done = KidsProgress::query()->where('child_profile_id', $child->id)->where('content_type', 'lesson')->whereIn('content_id', $course->lessons->pluck('id'))->pluck('stars', 'content_id');

        return view('app.kids.course', ['child' => $child, 'course' => $course->load('lessons'), 'done' => $done]);
    }

    public function lesson(Request $request, KidsCourse $course, KidsLesson $lesson)
    {
        $child = $this->child($request);
        $this->allowed($request, 'courses');
        abort_unless($lesson->kids_course_id === $course->id && $course->is_published, 404);
        $next = $course->lessons->firstWhere('sort', '>', $lesson->sort);

        return view('app.kids.lesson', ['child' => $child, 'course' => $course, 'lesson' => $lesson, 'next' => $next]);
    }

    public function completeLesson(Request $request, KidsCourse $course, KidsLesson $lesson)
    {
        $child = $this->child($request);
        $this->allowed($request, 'courses');
        abort_unless($lesson->kids_course_id === $course->id, 404);
        KidsProgress::query()->firstOrCreate(['child_profile_id' => $child->id, 'content_type' => 'lesson', 'content_id' => $lesson->id], ['stars' => 3, 'completed_at' => now()]);
        $next = $course->lessons->firstWhere('sort', '>', $lesson->sort);

        return $next ? redirect()->route('app.kids.lesson', [$course, $next])->with('status', 'Well done! You earned 3 stars.') : redirect()->route('app.kids.course', $course)->with('status', 'You finished the course. Masha Allah!');
    }

    public function story(Request $request, KidsStory $story)
    {
        $child = $this->child($request);
        $this->allowed($request, 'stories');
        abort_unless($story->is_published, 404);
        KidsProgress::query()->firstOrCreate(['child_profile_id' => $child->id, 'content_type' => 'story', 'content_id' => $story->id], ['stars' => 1, 'completed_at' => now()]);

        return view('app.kids.story', ['child' => $child, 'story' => $story]);
    }

    public function game(Request $request, KidsGame $game)
    {
        $child = $this->child($request);
        $this->allowed($request, 'games');
        abort_unless($game->is_published, 404);
        $best = KidsProgress::query()->where('child_profile_id', $child->id)->where('content_type', 'game')->where('content_id', $game->id)->first();

        return view('app.kids.game', ['child' => $child, 'game' => $game, 'best' => $best]);
    }

    public function completeGame(Request $request, KidsGame $game)
    {
        $child = $this->child($request);
        $this->allowed($request, 'games');
        $d = $request->validate(['score' => ['required', 'integer', 'between:0,100']]);
        $stars = $d['score'] >= 90 ? 3 : ($d['score'] >= 60 ? 2 : 1);
        $p = KidsProgress::query()->firstOrNew(['child_profile_id' => $child->id, 'content_type' => 'game', 'content_id' => $game->id]);
        $p->fill(['stars' => max($stars, (int) $p->stars), 'score' => max($d['score'], (int) $p->score), 'completed_at' => now()])->save();

        return response()->json(['stars' => $stars, 'total_stars' => (int) $child->progress()->sum('stars')]);
    }

    public function heartbeat(Request $request)
    {
        $child = $this->child($request);
        $d = $request->validate(['seconds' => ['required', 'integer', 'between:1,60']]);
        $row = DB::table('child_screen_time')->where('child_profile_id', $child->id)->where('day', today()->toDateString());
        $row->exists() ? $row->increment('seconds', $d['seconds']) : DB::table('child_screen_time')->insert(['child_profile_id' => $child->id, 'day' => today()->toDateString(), 'seconds' => $d['seconds'], 'created_at' => now(), 'updated_at' => now()]);
        $used = (int) DB::table('child_screen_time')->where('child_profile_id', $child->id)->where('day', today()->toDateString())->value('seconds');

        return response()->json(['remaining' => max(0, $child->controls->daily_limit_minutes * 60 - $used)]);
    }

    public function limit(Request $request)
    {
        return view('app.kids.limit', ['child' => $this->child($request)]);
    }

    public function exit(Request $request)
    {
        $request->validate(['password' => ['required', 'string']]);
        if (! Hash::check($request->input('password'), $request->user()->password)) {
            return back()->withErrors(['password' => 'That password is not correct.']);
        }
        $request->session()->forget('kids_child_id');

        return redirect()->route('app.family.index')->with('status', 'Kids mode ended.');
    }
}
