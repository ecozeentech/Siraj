<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Community\Comment;
use App\Models\Community\Group;
use App\Models\Community\ModerationAppeal;
use App\Models\Community\Post;
use App\Models\Community\Reaction;
use App\Models\Community\UserRestriction;
use App\Models\User;
use App\Services\Community\CommunityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CommunityController extends Controller
{
    public function __construct(private CommunityService $community) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $tab = in_array($request->query('tab'), ['following', 'popular'], true) ? $request->query('tab') : 'latest';
        $q = $this->community->visiblePosts($user)->whereNull('posts.group_id')->with(['author.profile', 'media', 'sharedPost.author', 'sharedPost.media']);
        if ($tab === 'following') {
            $q->whereIn('posts.user_id', DB::table('follows')->where('follower_id', $user->id)->select('followed_id'));
        }
        $posts = ($tab === 'popular' ? $q->where('posts.created_at', '>=', now()->subDays(14))->orderByDesc('reactions_count')->orderByDesc('posts.id') : $q->latest('posts.id'))->paginate(15)->withQueryString();

        return view('app.community.index', [
            'posts' => $posts, 'tab' => $tab, 'liked' => $this->likedIds($user, 'post', $posts->pluck('id')),
            'restriction' => $this->activeRestriction($user), 'myGroups' => Group::query()->whereIn('id', DB::table('group_members')->where('user_id', $user->id)->where('status', 'approved')->select('group_id'))->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_if($this->community->isRestricted($user, 'posting'), 403, 'Your posting privileges are restricted.');
        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:2000', 'required_without:images'], 'visibility' => ['required', Rule::in(['public', 'followers'])],
            'group_id' => ['nullable', 'integer'], 'images' => ['nullable', 'array', 'max:4'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=6000,max_height=6000'],
        ]);
        $group = null;
        if (! empty($data['group_id'])) {
            $group = Group::query()->where('status', 'active')->findOrFail($data['group_id']);
            abort_unless($this->isApprovedMember($user, $group), 403);
        }
        $post = $this->community->createPost($user, $data['body'] ?? null, $data['visibility'], $request->file('images', []), $group);

        return ($group ? redirect()->route('app.community.groups.show', $group) : redirect()->route('app.community.index'))->with('status', 'Posted.');
    }

    public function show(Request $request, Post $post)
    {
        abort_unless($this->community->canView($request->user(), $post), 404);
        $post->load(['author.profile', 'media', 'sharedPost.author', 'group']);
        $hidden = $this->community->hiddenAuthorIds($request->user());
        $comments = $post->comments()->whereNull('parent_id')->where('status', 'published')->whereNotIn('user_id', $hidden)->with(['author.profile', 'replies' => fn ($q) => $q->where('status', 'published')->whereNotIn('user_id', $hidden)->with('author')])->oldest('id')->paginate(30);

        return view('app.community.show', [
            'post' => $post, 'comments' => $comments, 'liked' => $this->likedIds($request->user(), 'post', collect([$post->id])),
            'likedComments' => $this->likedIds($request->user(), 'comment', $comments->pluck('id')->merge($comments->getCollection()->flatMap(fn ($c) => $c->replies)->pluck('id'))),
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        $this->authorize('delete', $post);
        $this->community->deletePostMedia($post);
        $post->delete();

        return redirect()->route('app.community.index')->with('status', 'Post deleted.');
    }

    public function share(Request $request, Post $post)
    {
        $user = $request->user();
        abort_unless($this->community->canView($user, $post) && $post->visibility === 'public' && ! $post->group_id, 404);
        abort_if($this->community->isRestricted($user, 'posting'), 403);
        $data = $request->validate(['body' => ['nullable', 'string', 'max:500']]);
        $this->community->createPost($user, $data['body'] ?? null, 'public', [], null, $post->shared_post_id ? $post->sharedPost : $post);

        return redirect()->route('app.community.index')->with('status', 'Shared to your feed.');
    }

    public function comment(Request $request, Post $post)
    {
        $user = $request->user();
        abort_unless($this->community->canView($user, $post), 404);
        abort_if($this->community->isRestricted($user, 'posting'), 403, 'Your posting privileges are restricted.');
        $data = $request->validate(['body' => ['required', 'string', 'max:1000'], 'parent_id' => ['nullable', 'integer']]);
        $this->community->addComment($user, $post, $data['body'], $data['parent_id'] ?? null);

        return back()->with('status', 'Comment added.');
    }

    public function destroyComment(Request $request, Comment $comment)
    {
        $user = $request->user();
        abort_unless($comment->user_id === $user->id || $comment->post->user_id === $user->id, 403);
        $this->community->deleteComment($comment);

        return back()->with('status', 'Comment deleted.');
    }

    public function react(Request $request, string $type, int $id)
    {
        $user = $request->user();
        $target = $type === 'post' ? Post::query()->findOrFail($id) : Comment::query()->findOrFail($id);
        $post = $target instanceof Post ? $target : $target->post;
        abort_unless($this->community->canView($user, $post), 404);
        $on = $this->community->toggleReaction($user, $target);
        $count = $target instanceof Post ? $target->fresh()->reactions_count : $target->reactions()->count();

        return $request->expectsJson() ? response()->json(['liked' => $on, 'count' => $count]) : back();
    }

    public function report(Request $request, string $type, int $id)
    {
        $data = $request->validate(['reason' => ['required', Rule::in(['spam', 'harassment', 'hate', 'misinformation', 'inappropriate', 'impersonation', 'other'])], 'details' => ['nullable', 'string', 'max:1000']]);
        $this->community->report($request->user(), $type, $id, $data['reason'], $data['details'] ?? null);

        return back()->with('status', 'Thank you. Our moderators will review this report.');
    }

    public function people(Request $request)
    {
        $user = $request->user();
        $q = trim((string) $request->query('q'));
        $hidden = $this->community->hiddenAuthorIds($user);
        $people = User::query()->with('profile')->where('status', 'active')->where('id', '!=', $user->id)->whereNotIn('id', $hidden)->where('is_staff', false)
            ->whereHas('profile', fn ($p) => $p->where('profile_visibility', '!=', 'private'))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%')->orWhereHas('profile', fn ($p) => $p->where('username', 'like', '%'.ltrim($q, '@').'%'))))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('app.community.people', ['people' => $people, 'q' => $q, 'following' => DB::table('follows')->where('follower_id', $user->id)->pluck('followed_id')->all()]);
    }

    public function profile(Request $request, User $user)
    {
        $viewer = $request->user();
        abort_if($this->community->hiddenAuthorIds($viewer)->contains($user->id) || $user->status !== 'active', 404);
        $isFollowing = DB::table('follows')->where(['follower_id' => $viewer->id, 'followed_id' => $user->id])->exists();
        $visibility = $user->profile?->profile_visibility ?? 'public';
        $canSee = $viewer->id === $user->id || $visibility === 'public' || ($visibility === 'followers' && $isFollowing);
        $posts = $canSee ? $this->community->visiblePosts($viewer)->where('posts.user_id', $user->id)->whereNull('posts.group_id')->with(['author.profile', 'media'])->latest('posts.id')->paginate(10) : null;

        return view('app.community.profile', [
            'person' => $user->load('profile'), 'canSee' => $canSee, 'posts' => $posts, 'isFollowing' => $isFollowing,
            'followers' => $user->followers()->count(), 'following' => $user->following()->count(),
            'liked' => $posts ? $this->likedIds($viewer, 'post', $posts->pluck('id')) : collect(),
        ]);
    }

    public function follow(Request $request, User $user)
    {
        $on = $this->community->follow($request->user(), $user);

        return back()->with('status', $on ? 'You are now following '.$user->name.'.' : 'Unfollowed.');
    }

    public function block(Request $request, User $user)
    {
        $this->community->block($request->user(), $user);

        return redirect()->route('app.community.index')->with('status', $user->name.' has been blocked. You will no longer see each other in the community or messages.');
    }

    public function unblock(Request $request, User $user)
    {
        DB::table('user_blocks')->where(['blocker_id' => $request->user()->id, 'blocked_id' => $user->id])->delete();

        return back()->with('status', 'User unblocked.');
    }

    public function appeal(Request $request)
    {
        $data = $request->validate(['message' => ['required', 'string', 'min:10', 'max:2000']]);
        $restriction = $this->activeRestriction($request->user());
        abort_unless($restriction, 422, 'You have no active restriction to appeal.');
        ModerationAppeal::query()->firstOrCreate(['user_id' => $request->user()->id, 'user_restriction_id' => $restriction->id, 'status' => 'open'], ['message' => $data['message']]);

        return back()->with('status', 'Your appeal has been sent to the moderation team.');
    }

    private function isApprovedMember(User $user, Group $group): bool
    {
        return DB::table('group_members')->where(['group_id' => $group->id, 'user_id' => $user->id, 'status' => 'approved'])->exists();
    }

    private function activeRestriction(User $user): ?UserRestriction
    {
        return UserRestriction::query()->where('user_id', $user->id)->whereNull('lifted_at')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->latest('id')->first();
    }

    private function likedIds(User $user, string $type, $ids)
    {
        return Reaction::query()->where('user_id', $user->id)->where('reactable_type', $type)->whereIn('reactable_id', $ids)->pluck('reactable_id');
    }
}
