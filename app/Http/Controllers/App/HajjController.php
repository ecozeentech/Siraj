<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Worship\Guide;
use App\Models\Worship\GuideChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HajjController extends Controller
{
    public function index(Request $request)
    {
        $saved = DB::table('saved_guides')->where('user_id', $request->user()->id)->pluck('guide_id')->flip();

        return view('app.hajj.index', ['guides' => Guide::query()->where('is_published', true)->withCount('steps', 'checklistItems')->orderBy('sort')->get(), 'saved' => $saved]);
    }

    public function show(Request $request, Guide $guide)
    {
        abort_unless($guide->is_published, 404);
        $user = $request->user();
        $items = $guide->checklistItems()->orderBy('sort')->get();
        $done = DB::table('checklist_progress')->where('user_id', $user->id)->whereIn('guide_checklist_item_id', $items->pluck('id'))->pluck('guide_checklist_item_id')->flip();

        return view('app.hajj.show', [
            'guide' => $guide->load(['steps' => fn ($q) => $q->orderBy('sort')]), 'items' => $items->groupBy('category'), 'done' => $done, 'itemCount' => $items->count(),
            'isSaved' => DB::table('saved_guides')->where('user_id', $user->id)->where('guide_id', $guide->id)->exists(),
        ]);
    }

    public function toggleSave(Request $request, Guide $guide)
    {
        $q = DB::table('saved_guides')->where('user_id', $request->user()->id)->where('guide_id', $guide->id);
        if ($q->exists()) {
            $q->delete();
        } else {
            DB::table('saved_guides')->insert(['user_id' => $request->user()->id, 'guide_id' => $guide->id, 'created_at' => now(), 'updated_at' => now()]);
        }

        return back()->with('status', 'Saved guides updated.');
    }

    public function toggleItem(Request $request, GuideChecklistItem $item)
    {
        $q = DB::table('checklist_progress')->where('user_id', $request->user()->id)->where('guide_checklist_item_id', $item->id);
        $checked = ! $q->exists();
        $checked ? DB::table('checklist_progress')->insert(['user_id' => $request->user()->id, 'guide_checklist_item_id' => $item->id, 'completed_at' => now()]) : $q->delete();

        return response()->json(['checked' => $checked]);
    }
}
