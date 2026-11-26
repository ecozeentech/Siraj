<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Community\Group;
use App\Models\Community\GroupMember;
use App\Models\Community\Reaction;
use App\Services\Community\CommunityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    public function __construct(private CommunityService $community) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $mine = GroupMember::query()->where('user_id', $user->id)->pluck('status', 'group_id');

        return view('app.community.groups', [
            'groups' => Group::query()->where('status', 'active')->withCount('posts')->orderByDesc('members_count')->paginate(18), 'mine' => $mine,
            'canCreate' => ! $this->community->isRestricted($user, 'posting'),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_if($this->community->isRestricted($user, 'posting'), 403);
        abort_if(Group::query()->where('owner_id', $user->id)->count() >= 5, 422, 'You can own up to 5 groups.');
        $data = $request->validate(['name' => ['required', 'string', 'min:3', 'max:80'], 'description' => ['nullable', 'string', 'max:500'], 'privacy' => ['required', Rule::in(['public', 'private'])]]);
        $group = DB::transaction(function () use ($user, $data) {
            $slug = Str::slug($data['name']) ?: 'group';
            while (Group::query()->where('slug', $slug)->exists()) {
                $slug = Str::slug($data['name']).'-'.Str::lower(Str::random(4));
            }
            $group = Group::query()->create($data + ['owner_id' => $user->id, 'slug' => $slug, 'members_count' => 1]);
            GroupMember::query()->create(['group_id' => $group->id, 'user_id' => $user->id, 'role' => 'owner', 'status' => 'approved']);

            return $group;
        });

        return redirect()->route('app.community.groups.show', $group)->with('status', 'Group created.');
    }

    public function show(Request $request, Group $group)
    {
        abort_if($group->status !== 'active', 404);
        $user = $request->user();
        $membership = GroupMember::query()->where(['group_id' => $group->id, 'user_id' => $user->id])->first();
        $approved = $membership?->status === 'approved';
        $canRead = $group->privacy === 'public' || $approved;
        $posts = $canRead ? $this->community->visiblePosts($user)->where('posts.group_id', $group->id)->with(['author.profile', 'media'])->latest('posts.id')->paginate(15) : null;
        $pending = $membership && $membership->role !== 'member' || $user->id === $group->owner_id ? GroupMember::query()->where('group_id', $group->id)->where('status', 'pending')->with('user')->get() : collect();

        return view('app.community.group', [
            'group' => $group, 'membership' => $membership, 'approved' => $approved, 'canRead' => $canRead, 'posts' => $posts, 'pending' => $pending,
            'liked' => $posts ? Reaction::query()->where('user_id', $user->id)->where('reactable_type', 'post')->whereIn('reactable_id', $posts->pluck('id'))->pluck('reactable_id') : collect(),
            'members' => $canRead ? GroupMember::query()->where('group_id', $group->id)->where('status', 'approved')->with('user')->limit(12)->get() : collect(),
        ]);
    }

    public function join(Request $request, Group $group)
    {
        abort_if($group->status !== 'active', 404);
        $user = $request->user();
        $status = $group->privacy === 'public' ? 'approved' : 'pending';
        $created = GroupMember::query()->firstOrCreate(['group_id' => $group->id, 'user_id' => $user->id], ['role' => 'member', 'status' => $status]);
        if ($created->wasRecentlyCreated && $status === 'approved') {
            $group->increment('members_count');
        }

        return back()->with('status', $status === 'approved' ? 'You joined the group.' : 'Request sent. A group admin will review it.');
    }

    public function leave(Request $request, Group $group)
    {
        $user = $request->user();
        abort_if($group->owner_id === $user->id, 422, 'Owners cannot leave their own group.');
        $m = GroupMember::query()->where(['group_id' => $group->id, 'user_id' => $user->id])->first();
        if ($m) {
            $wasApproved = $m->status === 'approved';
            $m->delete();
            if ($wasApproved) {
                $group->decrement('members_count');
            }
        }

        return redirect()->route('app.community.groups.index')->with('status', 'You left the group.');
    }

    public function approve(Request $request, Group $group, GroupMember $member)
    {
        $user = $request->user();
        abort_unless($member->group_id === $group->id && ($group->owner_id === $user->id || GroupMember::query()->where(['group_id' => $group->id, 'user_id' => $user->id, 'role' => 'moderator', 'status' => 'approved'])->exists()), 403);
        if ($member->status === 'pending') {
            $member->update(['status' => 'approved']);
            $group->increment('members_count');
        }

        return back()->with('status', 'Member approved.');
    }
}
