<?php

namespace App\Services\Finance;

use App\Models\Finance\PaymentTransaction;
use App\Models\Finance\Subscription;
use App\Models\Finance\SubscriptionPlan;
use App\Models\Finance\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionService
{
    public function __construct(private LedgerService $ledger) {}

    public function activate(User $user, SubscriptionPlan $plan, ?PaymentTransaction $tx = null, string $source = 'gateway'): Subscription
    {
        return DB::transaction(function () use ($user, $plan, $tx, $source) {
            Subscription::query()->where('user_id', $user->id)->where('status', 'active')
                ->update(['status' => 'cancelled', 'cancelled_at' => now(), 'ends_at' => now(), 'auto_renew' => false]);

            $starts = now();
            $ends = match ($plan->interval) {
                'month' => $starts->copy()->addMonth(),
                'year' => $starts->copy()->addYear(),
                default => null,
            };

            return Subscription::query()->create([
                'user_id' => $user->id, 'subscription_plan_id' => $plan->id, 'status' => 'active', 'starts_at' => $starts, 'ends_at' => $ends,
                'auto_renew' => false, 'payment_transaction_id' => $tx?->id, 'source' => $source,
            ]);
        });
    }

    /** Pay for a plan from the user's wallet balance (a real ledger movement; fails if funds are insufficient). */
    public function subscribeWithWallet(User $user, SubscriptionPlan $plan): Subscription
    {
        if ($plan->price_kobo <= 0) {
            return $this->activate($user, $plan, null, 'admin');
        }

        return DB::transaction(function () use ($user, $plan) {
            $wallet = Wallet::query()->where('user_id', $user->id)->firstOrFail();
            $tx = PaymentTransaction::query()->create([
                'user_id' => $user->id, 'reference' => 'SIRAJ-W'.Str::upper(Str::random(14)), 'gateway' => 'wallet', 'mode' => 'live',
                'purpose' => 'subscription', 'payable_type' => $plan->getMorphClass(), 'payable_id' => $plan->id,
                'amount_kobo' => $plan->price_kobo, 'currency' => $plan->currency, 'status' => 'succeeded', 'paid_at' => now(),
            ]);
            $this->ledger->post([
                ['account' => $this->ledger->ensureUserAccount($wallet), 'direction' => 'debit', 'amount' => $plan->price_kobo],
                ['account' => 'system:subscription_revenue', 'direction' => 'credit', 'amount' => $plan->price_kobo],
            ], 'Subscription: '.$plan->name, 'pay:'.$tx->reference, $tx, $plan->currency);

            return $this->activate($user, $plan, $tx, 'wallet');
        });
    }

    public function cancel(Subscription $subscription): void
    {
        $subscription->update(['auto_renew' => false, 'cancelled_at' => now()]);
    }

    public function expireDue(): int
    {
        return Subscription::query()->where('status', 'active')->whereNotNull('ends_at')->where('ends_at', '<=', now())->update(['status' => 'expired']);
    }
}
