<?php

namespace App\Services\Finance;

use App\Models\Events\Event;
use App\Models\Events\EventRegistration;
use App\Models\Finance\PaymentMethod;
use App\Models\Finance\PaymentTransaction;
use App\Models\Finance\SubscriptionPlan;
use App\Models\Finance\Wallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    public function __construct(private PaymentGateway $gateway, private LedgerService $ledger, private SubscriptionService $subscriptions) {}

    public function gateway(): PaymentGateway
    {
        return $this->gateway;
    }

    public function initiate(User $user, string $purpose, int $amountKobo, ?Model $payable = null, array $meta = []): PaymentTransaction
    {
        if (! $this->gateway->isConfigured()) {
            throw new GatewayNotConfiguredException('Online payments are not configured yet. Please try again later.');
        }
        if ($amountKobo < 5000) {
            throw new RuntimeException('The minimum payment is ₦50.');
        }

        $tx = PaymentTransaction::query()->create([
            'user_id' => $user->id, 'reference' => 'SIRAJ-'.Str::upper(Str::random(16)), 'gateway' => $this->gateway->name(),
            'mode' => $this->gateway->mode(), 'purpose' => $purpose, 'payable_type' => $payable?->getMorphClass(), 'payable_id' => $payable?->getKey(),
            'amount_kobo' => $amountKobo, 'currency' => config('siraj.currency'), 'status' => 'pending', 'meta' => $meta ?: null,
        ]);

        try {
            $init = $this->gateway->initialize($tx, $user->email, route('app.payments.callback'));
        } catch (\Throwable $e) {
            $tx->update(['status' => 'failed', 'gateway_response' => ['error' => $e->getMessage()]]);
            throw $e;
        }
        $tx->update(['authorization_url' => $init['authorization_url'], 'access_code' => $init['access_code']]);

        return $tx;
    }

    /** Verify with the gateway (callback return / manual re-check) and fulfil if paid. */
    public function verifyAndFulfil(PaymentTransaction $tx): PaymentTransaction
    {
        if ($tx->status === 'succeeded') {
            return $tx;
        }
        $result = $this->gateway->verify($tx->reference);

        return match ($result['status']) {
            'success' => $this->fulfil($tx, $result),
            'failed', 'abandoned' => tap($tx)->update(['status' => $result['status'], 'gateway_response' => $result['raw']]),
            default => $tx,
        };
    }

    /** Idempotent fulfilment of a paid transaction. */
    public function fulfil(PaymentTransaction $tx, array $result): PaymentTransaction
    {
        return DB::transaction(function () use ($tx, $result) {
            $tx = PaymentTransaction::query()->whereKey($tx->id)->lockForUpdate()->firstOrFail();
            if ($tx->status === 'succeeded') {
                return $tx;
            }
            if ((int) $result['amount'] !== (int) $tx->amount_kobo || strtoupper($result['currency']) !== strtoupper($tx->currency)) {
                $tx->update(['status' => 'failed', 'gateway_response' => ['error' => 'amount_or_currency_mismatch'] + $result['raw']]);

                return $tx;
            }

            $tx->update([
                'status' => 'succeeded', 'paid_at' => now(), 'channel' => $result['channel'] ?? null,
                'gateway_reference' => (string) ($result['raw']['id'] ?? $tx->reference), 'gateway_response' => $result['raw'],
            ]);

            $clearing = $this->ledger->systemAccount('system:gateway_clearing');
            $description = match ($tx->purpose) {
                'wallet_topup' => 'Wallet top-up',
                'subscription' => 'Subscription payment',
                'event_ticket' => 'Event ticket',
                default => 'Payment',
            };
            $wallet = Wallet::query()->where('user_id', $tx->user_id)->firstOrFail();

            switch ($tx->purpose) {
                case 'wallet_topup':
                    $this->ledger->post([
                        ['account' => $clearing, 'direction' => 'debit', 'amount' => $tx->amount_kobo],
                        ['account' => $this->ledger->ensureUserAccount($wallet), 'direction' => 'credit', 'amount' => $tx->amount_kobo],
                    ], $description, 'pay:'.$tx->reference, $tx);
                    break;
                case 'subscription':
                    $this->ledger->post([
                        ['account' => $clearing, 'direction' => 'debit', 'amount' => $tx->amount_kobo],
                        ['account' => 'system:subscription_revenue', 'direction' => 'credit', 'amount' => $tx->amount_kobo],
                    ], $description, 'pay:'.$tx->reference, $tx);
                    $plan = SubscriptionPlan::query()->findOrFail($tx->payable_id);
                    $this->subscriptions->activate($tx->user, $plan, $tx, 'gateway');
                    break;
                case 'event_ticket':
                    $this->ledger->post([
                        ['account' => $clearing, 'direction' => 'debit', 'amount' => $tx->amount_kobo],
                        ['account' => 'system:event_revenue', 'direction' => 'credit', 'amount' => $tx->amount_kobo],
                    ], $description, 'pay:'.$tx->reference, $tx);
                    EventRegistration::query()->where('event_id', $tx->payable_id)->where('user_id', $tx->user_id)->update(['status' => 'registered']);
                    break;
            }

            if ($auth = $result['authorization'] ?? null) {
                PaymentMethod::query()->updateOrCreate(
                    ['user_id' => $tx->user_id, 'gateway' => $this->gateway->name(), 'last4' => $auth['last4'] ?? null, 'exp_month' => $auth['exp_month'] ?? null, 'exp_year' => $auth['exp_year'] ?? null],
                    ['authorization_code' => $auth['authorization_code'], 'brand' => $auth['brand'] ?? null],
                );
            }

            return $tx;
        });
    }
}
