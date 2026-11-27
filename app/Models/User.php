<?php

namespace App\Models;

use App\Models\Family\Family;
use App\Models\Finance\Subscription;
use App\Models\Finance\Wallet;
use App\Models\Worship\PrayerPreference;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = ['id', 'is_staff'];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    private ?array $permissionCache = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'suspended_at' => 'datetime',
            'deletion_requested_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'onboarded_at' => 'datetime',
            'password' => 'hashed',
            'is_staff' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class)->withDefault();
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    public function prayerPreference(): HasOne
    {
        return $this->hasOne(PrayerPreference::class);
    }

    public function qiblaPreference(): HasOne
    {
        return $this->hasOne(Worship\QiblaPreference::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->latestOfMany('starts_at');
    }

    public function ownedFamilies(): HasMany
    {
        return $this->hasMany(Family::class, 'owner_id');
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followed_id')->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followed_id', 'follower_id')->withTimestamps();
    }

    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_blocks', 'blocker_id', 'blocked_id')->withTimestamps();
    }

    public function permissionSlugs(): array
    {
        return $this->permissionCache ??= $this->roles()->with('permissions:id,slug')->get()
            ->flatMap(fn (Role $role) => $role->slug === 'super-admin' ? ['*'] : $role->permissions->pluck('slug'))
            ->unique()->values()->all();
    }

    public function hasPermission(string $slug): bool
    {
        if (! $this->is_staff) {
            return false;
        }
        $slugs = $this->permissionSlugs();

        return in_array('*', $slugs, true) || in_array($slug, $slugs, true);
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles->contains('slug', $slug);
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_staff && $this->hasRole('super-admin');
    }

    public function flushPermissionCache(): void
    {
        $this->permissionCache = null;
        $this->unsetRelation('roles');
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function initials(): string
    {
        return collect(explode(' ', trim($this->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    }

    public function avatarUrl(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }
        $disk = Storage::disk('public');
        if (! $disk->exists($this->avatar_path)) {
            return null;
        }

        return $disk->url($this->avatar_path);
    }

    public function firstName(): string
    {
        return explode(' ', trim($this->name))[0] ?? $this->name;
    }

    public function plan(): ?Finance\SubscriptionPlan
    {
        return $this->activeSubscription?->plan;
    }

    public function isPremium(): bool
    {
        $plan = $this->plan();

        return $plan !== null && ($plan->price_kobo > 0 || $plan->interval === 'lifetime');
    }

    public function entitlement(string $key, mixed $default = null): mixed
    {
        $plan = $this->plan() ?? Finance\SubscriptionPlan::query()->where('interval', 'free')->first();

        return $plan?->entitlements[$key] ?? $default;
    }
}
