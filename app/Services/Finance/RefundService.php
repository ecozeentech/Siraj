<?php

namespace App\Services\Finance;

use App\Models\Finance\PaymentTransaction;
use App\Models\Finance\Refund;
use App\Models\Finance\Subscription;
use App\Models\Finance\Wallet;
use App\Models\User;
use App\Services\Admin\AuditLogger;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RefundService
{
    public function __construct(private LedgerService $ledger, private PaymentGateway $gateway) {}

    public function request(PaymentTransaction $tx, int $amountKobo, string $method, string $reason, User $by): Refund
    {
        $refundable = $tx->amount_kobo - (int) $tx->refunds()->whereIn('status', ['requested', 'processed'])->sum('amount_kobo');
        if (! in_array($tx->status, ['succeeded', 'partially_refunded'], true)) {
            throw new InvalidArgumentException('Only successful payments can be refunded.');
        }
        if ($amountKobo < 1 || $amountKobo > $refundable) {
            throw new InvalidArgumentException('Refund amount exceeds the refundable balance.');
        }
        if ($method === 'gateway' && $tx->gateway === 'wallet') {
            throw new InvalidArgumentException('Wallet payments can only be refunded to the wallet.');
        }

        return Refund::query()->create(['payment_transaction_id' => $tx->id, 'amount_kobo' => $amountKobo, 'method' => $method, 'reason' => $reason, 'status' => 'requested', 'requested_by' => $by->id]);
    }

    public function process(Refund $refund, User $by): Refund
    {
        if ($refund->status !== 'requested') {
            throw new InvalidArgumentException('This refund has already been handled.');
        }

        return DB::transaction(function () use ($refund, $by) {
            $refund = Refund::query()->whereKey($refund->id)->lockForUpdate()->firstOrFail();
            $tx = $refund->transaction()->lockForUpdate()->firstOrFail();
            $revenue = match ($tx->purpose) {
                'event_ticket' => 'system:event_revenue',
                default => 'system:subscription_revenue',
            };

            if ($refund->method === 'wallet') {
                $wallet = Wallet::query()->where('user_id', $tx->user_id)->firstOrFail();
                $this->ledger->post([
                    ['account' => $revenue, 'direction' => 'debit', 'amount' => $refund->amount_kobo],
                    ['account' => $this->ledger->ensureUserAccount($wallet), 'direction' => 'credit', 'amount' => $refund->amount_kobo],
                ], 'Refund to wallet', 'refund:'.$refund->id, $refund, $tx->currency);
            } else {
                $result = app(GatewayCatalog::class)->make($tx->gateway)->refund($tx, $refund->amount_kobo, $refund->reason);
                $this->ledger->post([
                    ['account' => $revenue, 'direction' => 'debit', 'amount' => $refund->amount_kobo],
                    ['account' => 'system:gateway_clearing', 'direction' => 'credit', 'amount' => $refund->amount_kobo],
                ], 'Refund via gateway', 'refund:'.$refund->id, $refund, $tx->currency);
                $refund->gateway_refund_id = $result['id'];
            }

            $refund->forceFill(['status' => 'processed', 'processed_by' => $by->id, 'processed_at' => now()])->save();
            $totalRefunded = (int) $tx->refunds()->where('status', 'processed')->sum('amount_kobo');
            $tx->update(['status' => $totalRefunded >= $tx->amount_kobo ? 'refunded' : 'partially_refunded']);

            if ($tx->purpose === 'subscription' && $totalRefunded >= $tx->amount_kobo) {
                Subscription::query()->where('payment_transaction_id', $tx->id)->where('status', 'active')->update(['status' => 'cancelled', 'cancelled_at' => now(), 'ends_at' => now()]);
            }
            if ($tx->purpose === 'event_ticket' && $totalRefunded >= $tx->amount_kobo) {
                \App\Models\Events\EventRegistration::query()->where('event_id', $tx->payable_id)->where('user_id', $tx->user_id)->whereIn('status', ['registered', 'pending_payment'])->update(['status' => 'cancelled']);
            }
            AuditLogger::log('refund.processed', $refund, "Refund of {$refund->amount_kobo} kobo processed", ['method' => $refund->method]);

            return $refund;
        });
    }

    public function reject(Refund $refund, User $by, string $why): Refund
    {
        $refund->update(['status' => 'rejected', 'processed_by' => $by->id, 'processed_at' => now(), 'failure_reason' => $why]);

        return $refund;
    }
}
