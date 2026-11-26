<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Finance\PaymentTransaction;
use App\Services\Finance\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function callback(Request $request, PaymentService $payments)
    {
        $reference = (string) ($request->query('reference') ?: $request->query('trxref'));
        $tx = PaymentTransaction::query()->where('reference', $reference)->where('user_id', $request->user()->id)->firstOrFail();

        try {
            $tx = $payments->verifyAndFulfil($tx);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('app.wallet.index')->with('error', 'We could not confirm your payment yet. If you were charged it will be applied automatically within a few minutes.');
        }

        $dest = match ($tx->purpose) {
            'event_ticket' => redirect()->route('app.events.mine'),
            'subscription' => redirect()->route('app.subscription.index'),
            default => redirect()->route('app.wallet.index'),
        };

        return match ($tx->status) {
            'succeeded' => $dest->with('status', 'Payment successful. Thank you.'),
            'failed', 'abandoned' => $dest->with('error', 'The payment was not completed. You have not been charged.'),
            default => $dest->with('error', 'Your payment is still being processed. We will update your account as soon as it is confirmed.'),
        };
    }
}
