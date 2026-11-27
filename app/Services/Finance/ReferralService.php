<?php

namespace App\Services\Finance;

use App\Models\Finance\PointEntry;
use App\Models\Finance\ReferralAccount;
use App\Models\Finance\SubscriptionPlan;
use App\Models\User;
use App\Services\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ReferralService
{
    public function __construct(private SubscriptionService $subscriptions) {}

    public function account(User $user): ReferralAccount
    {
        return ReferralAccount::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['code' => $this->code()],
        );
    }

    public function enabled(): bool
    {
        return Settings::get('referrals.enabled', '1') !== '0';
    }

    public function pointsPerSignup(): int
    {
        return max(0, (int) Settings::get('referrals.points_per_signup', 100));
    }

    public function pointsToRedeem(): int
    {
        return max(1, (int) Settings::get('referrals.points_to_redeem', 500));
    }

    public function redeemPlan(): ?SubscriptionPlan
    {
        $id = Settings::get('referrals.plan_id');
        $query = SubscriptionPlan::query()->where('is_active', true)->where('price_kobo', '>', 0);

        return $id ? $query->whereKey($id)->first() : $query->orderBy('sort')->first();
    }

    public function attribute(User $newUser, ?string $code): void
    {
        if (! $this->enabled() || ! $code) {
            return;
        }
        $account = ReferralAccount::query()->where('code', strtoupper($code))->first();
        if (! $account || $account->user_id === $newUser->id) {
            return;
        }
        if (PointEntry::query()->where('user_id', $account->user_id)->where('reason', 'signup')->where('subject_id', $newUser->id)->exists()) {
            return;
        }
        $points = $this->pointsPerSignup();
        if ($points < 1) {
            return;
        }
        DB::transaction(function () use ($account, $newUser, $points) {
            $account = ReferralAccount::query()->whereKey($account->id)->lockForUpdate()->first();
            $account->increment('balance', $points);
            PointEntry::query()->create([
                'user_id' => $account->user_id, 'points' => $points, 'reason' => 'signup',
                'subject_type' => $newUser->getMorphClass(), 'subject_id' => $newUser->id,
            ]);
        });
    }

    public function redeem(User $user): \App\Models\Finance\Subscription
    {
        if (! $this->enabled()) {
            throw new InvalidArgumentException('Referrals are turned off.');
        }
        $plan = $this->redeemPlan();
        if (! $plan) {
            throw new InvalidArgumentException('No subscription can be redeemed yet.');
        }
        $cost = $this->pointsToRedeem();

        return DB::transaction(function () use ($user, $plan, $cost) {
            $account = ReferralAccount::query()->where('user_id', $user->id)->lockForUpdate()->first();
            if (! $account || $account->balance < $cost) {
                throw new InvalidArgumentException('You do not have enough points for this plan.');
            }
            $account->decrement('balance', $cost);
            PointEntry::query()->create([
                'user_id' => $user->id, 'points' => -$cost, 'reason' => 'redeem',
                'subject_type' => $plan->getMorphClass(), 'subject_id' => $plan->id,
            ]);

            return $this->subscriptions->activate($user, $plan, null, 'points');
        });
    }

    private function code(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (ReferralAccount::query()->where('code', $code)->exists());

        return $code;
    }
}
