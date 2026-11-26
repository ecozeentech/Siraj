<?php

namespace App\Providers;

use App\Models\Community\Post;
use App\Models\Family\ChildProfile;
use App\Models\Messaging\Conversation;
use App\Models\Ai\AiConversation;
use App\Policies\AiConversationPolicy;
use App\Policies\ChildProfilePolicy;
use App\Policies\ConversationPolicy;
use App\Policies\PostPolicy;
use App\Services\Finance\PaymentGateway;
use App\Services\Finance\PaystackGateway;
use App\Support\Permissions;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, PaystackGateway::class);
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.siraj');
        Password::defaults(fn () => Password::min(8)->letters()->numbers()->uncompromised(3));

        foreach (Permissions::slugs() as $slug) {
            Gate::define($slug, fn ($user) => $user->hasPermission($slug));
        }
        Relation::morphMap([
            'user' => \App\Models\User::class, 'post' => Post::class, 'comment' => \App\Models\Community\Comment::class,
            'message' => \App\Models\Messaging\Message::class, 'mosque' => \App\Models\Directory\Mosque::class,
            'business' => \App\Models\Directory\Business::class, 'review' => \App\Models\Directory\BusinessReview::class,
        ]);
        Gate::policy(AiConversation::class, AiConversationPolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(ChildProfile::class, ChildProfilePolicy::class);

        RateLimiter::for('login', fn (Request $r) => [
            Limit::perMinute(5)->by(strtolower((string) $r->input('email')).'|'.$r->ip()),
            Limit::perMinute(30)->by($r->ip()),
        ]);
        RateLimiter::for('register', fn (Request $r) => Limit::perHour(10)->by($r->ip()));
        RateLimiter::for('ai', fn (Request $r) => Limit::perMinute(10)->by($r->user()?->id ?: $r->ip()));
        RateLimiter::for('posting', fn (Request $r) => Limit::perMinute(10)->by($r->user()?->id ?: $r->ip()));
        RateLimiter::for('sync', fn (Request $r) => Limit::perMinute(60)->by($r->user()?->id ?: $r->ip()));
        RateLimiter::for('webhooks', fn (Request $r) => Limit::perMinute(120)->by($r->ip()));
        RateLimiter::for('audio', fn (Request $r) => Limit::perMinute(240)->by($r->ip()));
    }
}
