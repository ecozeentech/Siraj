<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Family\ChildProfile;
use App\Models\Family\Family;
use App\Models\Family\FamilyMember;
use App\Models\Family\ParentalControl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Parent-side management of child profiles. Every child is checked against the family owned by the signed-in user. */
class FamilyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $family = $user->ownedFamilies()->first();
        $children = $family ? $family->children()->with('controls')->get() : collect();

        $stats = $children->mapWithKeys(function (ChildProfile $c) {
            $progress = $c->progress()->get();

            return [$c->id => [
                'stars' => (int) $progress->sum('stars'), 'lessons' => $progress->where('content_type', 'lesson')->whereNotNull('completed_at')->count(),
                'stories' => $progress->where('content_type', 'story')->count(), 'games' => $progress->where('content_type', 'game')->count(),
                'today' => (int) DB::table('child_screen_time')->where('child_profile_id', $c->id)->where('day', today()->toDateString())->value('seconds'),
                'week' => (int) DB::table('child_screen_time')->where('child_profile_id', $c->id)->where('day', '>=', today()->subDays(6)->toDateString())->sum('seconds'),
            ]];
        });

        return view('app.family.index', ['children' => $children, 'stats' => $stats, 'max' => (int) $user->entitlement('family_children', 2)]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $d = $request->validate(['name' => ['required', 'string', 'max:60'], 'birth_year' => ['required', 'integer', 'between:'.(now()->year - 17).','.now()->year], 'avatar' => ['required', 'in:boy,girl']]);
        $family = $user->ownedFamilies()->first() ?? tap(Family::query()->create(['owner_id' => $user->id, 'name' => $user->firstName()."'s family"]), fn ($f) => FamilyMember::query()->create(['family_id' => $f->id, 'user_id' => $user->id, 'role' => 'parent']));

        if ($family->children()->count() >= (int) $user->entitlement('family_children', 2)) {
            return back()->with('error', 'Your plan allows up to '.$user->entitlement('family_children', 2).' child profiles. Upgrade to add more.');
        }
        $child = $family->children()->create($d);
        ParentalControl::query()->create(['child_profile_id' => $child->id, 'daily_limit_minutes' => 60, 'allowed_sections' => ['courses', 'stories', 'games']]);

        return back()->with('status', "{$child->name}'s profile was created.");
    }

    public function update(Request $request, ChildProfile $child)
    {
        $this->authorize('manage', $child);
        $d = $request->validate([
            'name' => ['required', 'string', 'max:60'], 'birth_year' => ['required', 'integer', 'between:'.(now()->year - 17).','.now()->year], 'avatar' => ['required', 'in:boy,girl'],
            'daily_limit_minutes' => ['required', 'integer', 'between:10,480'], 'allowed_sections' => ['nullable', 'array'], 'allowed_sections.*' => ['in:courses,stories,games'],
        ]);
        $child->update(collect($d)->only(['name', 'birth_year', 'avatar'])->all());
        ParentalControl::query()->updateOrCreate(['child_profile_id' => $child->id], ['daily_limit_minutes' => $d['daily_limit_minutes'], 'allowed_sections' => array_values($d['allowed_sections'] ?? [])]);

        return back()->with('status', 'Profile and controls saved.');
    }

    public function destroy(Request $request, ChildProfile $child)
    {
        $this->authorize('manage', $child);
        if ($request->session()->get('kids_child_id') === $child->id) {
            $request->session()->forget('kids_child_id');
        }
        $child->delete();

        return back()->with('status', 'Child profile deleted.');
    }

    public function enter(Request $request, ChildProfile $child)
    {
        $this->authorize('manage', $child);
        $request->session()->put('kids_child_id', $child->id);

        return redirect()->route('app.kids.home');
    }
}
