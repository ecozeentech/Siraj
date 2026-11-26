<?php

namespace App\Http\Controllers;

use App\Models\Finance\PaymentTransaction;
use App\Services\Finance\PaymentGateway;
use App\Services\Finance\PaymentService;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function paystack(Request $request, PaymentGateway $gateway, PaymentService $payments)
    {
        if (! $gateway->validWebhookSignature($request->getContent(), $request->header('x-paystack-signature'))) {
            return response('Invalid signature', 401);
        }

        $event = $request->json('event');
        $reference = $request->json('data.reference');
        if ($event === 'charge.success' && $reference && ($tx = PaymentTransaction::query()->where('reference', $reference)->first())) {
            // Never trust the payload amount: re-verify with the gateway, then fulfil idempotently.
            try {
                $payments->verifyAndFulfil($tx);
            } catch (\Throwable $e) {
                report($e);

                return response('Retry later', 500);
            }
        }

        return response('ok');
    }
}
