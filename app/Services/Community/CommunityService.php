<?php

namespace App\Services\Community;

use App\Models\Community\Comment;
use App\Models\Community\ContentReport;
use App\Models\Community\Group;
use App\Models\Community\Post;
use App\Models\Community\Reaction;
use App\Models\Community\UserRestriction;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommunityService
{
    public function isRestricted(User $user, string $type): bool
    {
        return UserRestriction::query()->where('user_id', $user->id)->whereNull('lifted_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->whereIn('type', [$type, 'all'])->exists();
    }

    /** Ids the viewer must never see or interact with (blocked by them or blocking them). */
    public function hiddenAuthorIds(User $viewer): Collection
    {
        return once(fn () => DB::table('user_blocks')->where('blocker_id', $viewer->id)->pluck('blocked_id')
            ->merge(DB::table('user_blocks')->where('blocked_id', $viewer->id)->pluck('blocker_id'))->unique()->values());
    }

    public function visiblePosts(User $viewer): Builder
    {
        $followed = DB::table('follows')->where('follower_id', $viewer->id)->pluck('followed_id');
        $groups = DB::table('group_members')->where('user_id', $viewer->id)->where('status', 'approved')->pluck('group_id');

        return Post::query()->where('posts.status', 'published')
            ->whereNotIn('posts.user_id', $this->hiddenAuthorIds($viewer))
            ->where(fn ($q) => $q->where('posts.user_id', $viewer->id)->orWhere('posts.visibility', 'public')->orWhereIn('posts.user_id', $followed))
            ->where(fn ($q) => $q->whereNull('posts.group_id')->orWhereIn('posts.group_id', $groups)
                ->orWhereIn('posts.group_id', Group::query()->where('privacy', 'public')->where('status', 'active')->select('id')));
    }

    public function canView(User $viewer, Post $post): bool
    {
        return $post->status === 'published' && $this->visiblePosts($viewer)->whereKey($post->id)->exists();
    }

    /** @param array<int, \Illuminate\Http\UploadedFile> $images */
    public function createPost(User $user, ?string $body, string $visibility, array $images = [], ?Group $group = null, ?Post $shared = null): Post
    {
        return DB::transaction(function () use ($user, $body, $visibility, $images, $group, $shared) {
            $post = Post::query()->create([
                'user_id' => $user->id, 'group_id' => $group?->id, 'shared_post_id' => $shared?->id,
                'body' => $this->clean($body), 'visibility' => $group ? 'public' : $visibility,
            ]);
            foreach (array_values($images) as $i => $file) {
                $path = $file->storeAs('posts/'.now()->format('Y/m'), Str::random(40).'.'.($file->guessExtension() ?: 'jpg'), 'public');
                $post->media()->create(['path' => $path, 'mime' => $file->getMimeType(), 'sort' => $i]);
            }
            if ($shared) {
                $shared->increment('shares_count');
            }

            return $post;
        });
    }

    public function addComment(User $user, Post $post, string $body, ?int $parentId = null): Comment
    {
        $parent = $parentId ? Comment::query()->where('post_id', $post->id)->whereNull('parent_id')->findOrFail($parentId) : null;
        $comment = DB::transaction(function () use ($user, $post, $body, $parent) {
            $c = Comment::query()->create(['post_id' => $post->id, 'user_id' => $user->id, 'parent_id' => $parent?->id, 'body' => $this->clean($body)]);
            $post->increment('comments_count');

            return $c;
        });
        if ($post->user_id !== $user->id) {
            $post->author->notify(new GeneralNotification($user->name.' commented on your post', Str::limit($comment->body, 120), route('app.community.posts.show', $post, false), 'community', 'message-square'));
        }

        return $comment;
    }

    public function deleteComment(Comment $comment): void
    {
        DB::transaction(function () use ($comment) {
            $count = 1 + $comment->replies()->count();
            $comment->replies()->delete();
            $comment->delete();
            Post::query()->whereKey($comment->post_id)->where('comments_count', '>=', $count)->decrement('comments_count', $count);
        });
    }

    /** @return bool true when the reaction is now on */
    public function toggleReaction(User $user, Model $target): bool
    {
        return DB::transaction(function () use ($user, $target) {
            $existing = Reaction::query()->where(['user_id' => $user->id, 'reactable_type' => $target->getMorphClass(), 'reactable_id' => $target->getKey()])->first();
            if ($existing) {
                $existing->delete();
                if ($target instanceof Post) {
                    Post::query()->whereKey($target->id)->where('reactions_count', '>', 0)->decrement('reactions_count');
                }

                return false;
            }
            Reaction::query()->create(['user_id' => $user->id, 'reactable_type' => $target->getMorphClass(), 'reactable_id' => $target->getKey(), 'type' => 'heart']);
            if ($target instanceof Post) {
                $target->increment('reactions_count');
            }

            return true;
        });
    }

    public function follow(User $user, User $target): bool
    {
        abort_if($user->id === $target->id, 422, 'You cannot follow yourself.');
        abort_if($this->hiddenAuthorIds($user)->contains($target->id), 403);
        $existing = DB::table('follows')->where(['follower_id' => $user->id, 'followed_id' => $target->id]);
        if ($existing->exists()) {
            $existing->delete();

            return false;
        }
        DB::table('follows')->insert(['follower_id' => $user->id, 'followed_id' => $target->id, 'created_at' => now(), 'updated_at' => now()]);
        $target->notify(new GeneralNotification($user->name.' started following you', 'Visit their profile to follow back.', route('app.community.profile', $user, false), 'community', 'user-plus'));

        return true;
    }

    public function block(User $user, User $target): void
    {
        abort_if($user->id === $target->id, 422, 'You cannot block yourself.');
        DB::transaction(function () use ($user, $target) {
            DB::table('user_blocks')->insertOrIgnore(['blocker_id' => $user->id, 'blocked_id' => $target->id, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('follows')->where(fn ($q) => $q->where('follower_id', $user->id)->where('followed_id', $target->id))->orWhere(fn ($q) => $q->where('follower_id', $target->id)->where('followed_id', $user->id))->delete();
        });
    }

    public function report(User $reporter, string $type, int $id, string $reason, ?string $details): ContentReport
    {
        $subject = match ($type) {
            'post' => Post::query()->findOrFail($id),
            'comment' => Comment::query()->findOrFail($id),
            'user' => User::query()->findOrFail($id),
        };
        $reported = match (true) { $subject instanceof User => $subject->id, default => $subject->user_id };
        abort_if($reported === $reporter->id, 422, 'You cannot report yourself.');

        return ContentReport::query()->firstOrCreate(
            ['reporter_id' => $reporter->id, 'reportable_type' => $subject->getMorphClass(), 'reportable_id' => $subject->getKey(), 'status' => 'open'],
            ['reported_user_id' => $reported, 'reason' => $reason, 'details' => $details],
        );
    }

    public function deletePostMedia(Post $post): void
    {
        foreach ($post->media as $m) {
            Storage::disk('public')->delete($m->path);
        }
    }

    private function clean(?string $text): ?string
    {
        $text = trim(strip_tags((string) $text));

        return $text === '' ? null : $text;
    }
}
