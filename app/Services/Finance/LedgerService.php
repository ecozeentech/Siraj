<?php

namespace App\Services\Finance;

use App\Models\Finance\LedgerEntry;
use App\Models\Finance\Wallet;
use App\Models\Finance\WalletAccount;
use App\Models\Finance\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Double-entry ledger. Every money movement is a group of ledger entries whose debits equal credits.
 * Entries are immutable (the model refuses update/delete). Wallet balances are cached on `wallets`
 * and always reconcilable against the ledger via balanceOf()/reconcile().
 */
class LedgerService
{
    public const SYSTEM_ACCOUNTS = [
        'system:gateway_clearing' => ['Payment gateway clearing', 'asset'],
        'system:subscription_revenue' => ['Subscription revenue', 'revenue'],
        'system:event_revenue' => ['Event ticket revenue', 'revenue'],
        'system:adjustments' => ['Manual adjustments', 'expense'],
    ];

    public function ensureUserAccount(Wallet $wallet): WalletAccount
    {
        return WalletAccount::query()->firstOrCreate(
            ['code' => "user:{$wallet->user_id}:wallet"],
            ['name' => "Wallet #{$wallet->user_id}", 'type' => 'liability', 'wallet_id' => $wallet->id, 'currency' => $wallet->currency],
        );
    }

    public function systemAccount(string $code): WalletAccount
    {
        [$name, $type] = self::SYSTEM_ACCOUNTS[$code] ?? throw new InvalidArgumentException("Unknown system account {$code}");

        return WalletAccount::query()->firstOrCreate(['code' => $code], ['name' => $name, 'type' => $type, 'currency' => config('siraj.currency')]);
    }

    /**
     * Post a balanced group of entries.
     *
     * @param  array<int, array{account: WalletAccount|string, direction: 'debit'|'credit', amount: int}>  $lines
     * @return string the ledger group id
     */
    public function post(array $lines, string $description, ?string $reference = null, ?Model $source = null, string $currency = 'NGN'): string
    {
        if ($reference && ($existing = LedgerEntry::query()->where('reference', $reference)->value('group_id'))) {
            return $existing; // idempotent replay (e.g. duplicate webhook)
        }

        $debits = collect($lines)->where('direction', 'debit')->sum('amount');
        $credits = collect($lines)->where('direction', 'credit')->sum('amount');
        if ($debits <= 0 || $debits !== $credits) {
            throw new InvalidArgumentException("Unbalanced ledger entry: debits {$debits} / credits {$credits}.");
        }
        foreach ($lines as $line) {
            if ($line['amount'] <= 0) {
                throw new InvalidArgumentException('Ledger amounts must be positive.');
            }
        }

        return DB::transaction(function () use ($lines, $description, $reference, $source, $currency) {
            $group = (string) Str::uuid();
            $now = now();

            // Lock wallets in a stable order to avoid deadlocks.
            $resolved = collect($lines)->map(function ($l) {
                $l['account'] = $l['account'] instanceof WalletAccount ? $l['account'] : $this->systemAccount($l['account']);

                return $l;
            });
            $walletIds = $resolved->pluck('account.wallet_id')->filter()->unique()->sort()->values();
            $wallets = Wallet::query()->whereIn('id', $walletIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($resolved as $i => $line) {
                /** @var WalletAccount $account */
                $account = $line['account'];
                LedgerEntry::query()->create([
                    'group_id' => $group, 'wallet_account_id' => $account->id, 'direction' => $line['direction'],
                    'amount_kobo' => $line['amount'], 'currency' => $currency, 'description' => $description,
                    'reference' => $i === 0 ? $reference : null, 'created_at' => $now,
                ]);

                if ($account->wallet_id) {
                    $wallet = $wallets[$account->wallet_id];
                    // A user wallet is a liability account: credits increase it, debits decrease it.
                    $delta = $line['direction'] === 'credit' ? $line['amount'] : -$line['amount'];
                    if ($wallet->balance_kobo + $delta < 0) {
                        throw new InsufficientFundsException('Insufficient wallet balance.');
                    }
                    $wallet->balance_kobo += $delta;
                    $wallet->save();
                    WalletTransaction::query()->create([
                        'wallet_id' => $wallet->id, 'ledger_group_id' => $group, 'type' => $line['direction'],
                        'amount_kobo' => $line['amount'], 'balance_after_kobo' => $wallet->balance_kobo, 'description' => $description,
                        'reference' => ($reference ?: $group).':w'.$wallet->id, 'source_type' => $source?->getMorphClass(), 'source_id' => $source?->getKey(),
                        'created_at' => $now,
                    ]);
                }
            }

            return $group;
        });
    }

    public function balanceOf(WalletAccount $account): int
    {
        $debits = (int) $account->entries()->where('direction', 'debit')->sum('amount_kobo');
        $credits = (int) $account->entries()->where('direction', 'credit')->sum('amount_kobo');

        return in_array($account->type, ['liability', 'revenue'], true) ? $credits - $debits : $debits - $credits;
    }

    public function reconcile(Wallet $wallet): bool
    {
        $account = $wallet->account;

        return $account && $this->balanceOf($account) === (int) $wallet->fresh()->balance_kobo;
    }
}
