<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finance\PaymentTransaction;
use App\Models\Finance\Refund;
use App\Models\Finance\Subscription;
use App\Models\Finance\Wallet;
use App\Models\User;
use App\Services\Admin\AuditLogger;
use App\Services\Finance\InsufficientFundsException;
use App\Services\Finance\LedgerService;
use App\Services\Finance\RefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FinanceController extends Controller
{
    public function payments(Request $request)
    {
        return view('admin.finance.payments', [
            'payments' => PaymentTransaction::query()->with('user')->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))->latest()->paginate(20, ['*'], 'payments')->withQueryString(),
            'refunds' => Refund::query()->with('transaction.user')->latest()->paginate(15, ['*'], 'refunds'),
        ]);
    }

    public function refund(Request $request, Refund $refund, RefundService $refunds)
    {
        $data = $request->validate(['decision' => ['required', 'in:process,reject'], 'why' => ['nullable', 'string', 'max:500']]);
        $actor = $request->user('admin');
        try {
            if ($data['decision'] === 'process') {
                $refunds->process($refund, $actor);
                $message = 'Refund processed and recorded in the ledger.';
            } else {
                $refunds->reject($refund, $actor, $data['why'] ?: 'Declined');
                $message = 'Refund request declined.';
            }
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $message);
    }

    public function adjust(Request $request, LedgerService $ledger)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'direction' => ['required', Rule::in(['credit', 'debit'])],
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);
        $user = User::query()->where('email', strtolower($data['email']))->firstOrFail();
        $wallet = Wallet::query()->where('user_id', $user->id)->firstOrFail();
        $kobo = (int) round($data['amount'] * 100);
        $userLine = ['account' => $ledger->ensureUserAccount($wallet), 'direction' => $data['direction'], 'amount' => $kobo];
        $system = ['account' => 'system:adjustments', 'direction' => $data['direction'] === 'credit' ? 'debit' : 'credit', 'amount' => $kobo];
        try {
            $ledger->post([$system, $userLine], 'Adjustment: '.$data['reason'], 'adj:'.Str::uuid(), null);
        } catch (InsufficientFundsException) {
            return back()->with('error', 'That debit is larger than the wallet balance. Nothing was changed.');
        }
        AuditLogger::log('finance.adjusted', $wallet, $data['reason'], ['direction' => $data['direction'], 'amount_kobo' => $kobo, 'user_id' => $user->id]);

        return back()->with('status', 'Wallet '.($data['direction'] === 'credit' ? 'credited' : 'debited').' for '.$user->name.'.');
    }

    public function subscriptions()
    {
        return view('admin.finance.subscriptions', [
            'subscriptions' => Subscription::query()->with(['user', 'plan'])->latest()->paginate(20),
        ]);
    }
}
