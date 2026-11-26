<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Finance\PaymentTransaction;
use App\Models\Finance\Refund;
use App\Models\Finance\WalletTransaction;
use App\Services\Finance\GatewayNotConfiguredException;
use App\Services\Finance\PaymentGateway;
use App\Services\Finance\PaymentService;
use App\Services\Finance\RefundService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WalletController extends Controller
{
    public function index(Request $request, PaymentGateway $gateway)
    {
        $user = $request->user();
        $wallet = $user->wallet;

        return view('app.wallet.index', [
            'wallet' => $wallet, 'gatewayReady' => $gateway->isConfigured(), 'mode' => $gateway->isConfigured() ? $gateway->mode() : null,
            'entries' => WalletTransaction::query()->where('wallet_id', $wallet->id)->latest('id')->paginate(10, ['*'], 'entries'),
            'payments' => PaymentTransaction::query()->where('user_id', $user->id)->with('refunds')->latest('id')->paginate(10, ['*'], 'payments'),
            'subscription' => $user->activeSubscription?->load('plan'),
        ]);
    }

    public function topUp(Request $request, PaymentService $payments)
    {
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:100', 'max:1000000']]);
        try {
            $tx = $payments->initiate($request->user(), 'wallet_topup', (int) round($data['amount'] * 100));
        } catch (GatewayNotConfiguredException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['amount' => 'We could not start the payment. Please try again.']);
        }

        return redirect()->away($tx->authorization_url);
    }

    public function receipt(Request $request, PaymentTransaction $transaction)
    {
        abort_unless($transaction->user_id === $request->user()->id, 404);

        return view('app.wallet.receipt', ['tx' => $transaction->load('refunds'), 'payable' => $transaction->payable]);
    }

    public function requestRefund(Request $request, PaymentTransaction $transaction, RefundService $refunds)
    {
        abort_unless($transaction->user_id === $request->user()->id, 404);
        abort_unless(in_array($transaction->purpose, ['subscription', 'event_ticket'], true), 422, 'Only subscriptions and event tickets can be refunded.');
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500'], 'method' => ['required', Rule::in(['wallet', 'gateway'])]]);
        abort_if(Refund::query()->where('payment_transaction_id', $transaction->id)->where('status', 'requested')->exists(), 422, 'A refund request is already pending for this payment.');
        try {
            $refunds->request($transaction, (int) $transaction->amount_kobo - (int) $transaction->refunds()->whereIn('status', ['requested', 'processed'])->sum('amount_kobo'), $data['method'], $data['reason'], $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['reason' => $e->getMessage()]);
        }

        return back()->with('status', 'Refund requested. Our team will review it and notify you.');
    }
}
