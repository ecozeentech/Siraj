<?php

namespace App\Services\Finance;

use App\Models\Finance\PaymentTransaction;
use App\Services\Settings;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripeGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'stripe';
    }

    private function secret(): ?string
    {
        return Settings::get('gateway.stripe.secret');
    }

    public function isConfigured(): bool
    {
        return filled($this->secret());
    }

    public function mode(): string
    {
        return str_starts_with((string) $this->secret(), 'sk_live_') ? 'live' : 'sandbox';
    }

    public function initialize(PaymentTransaction $tx, string $email, string $callbackUrl): array
    {
        $res = Http::withToken($this->secret())->asForm()->acceptJson()->timeout(20)->post('https://api.stripe.com/v1/checkout/sessions', [
            'mode' => 'payment',
            'customer_email' => $email,
            'client_reference_id' => $tx->reference,
            'success_url' => $callbackUrl.'?reference='.$tx->reference.'&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('app.wallet.index'),
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower($tx->currency),
                    'unit_amount' => $tx->amount_kobo,
                    'product_data' => ['name' => 'SIRAJ '.str_replace('_', ' ', $tx->purpose)],
                ],
            ]],
        ]);
        if (! $res->successful() || ! $res->json('url')) {
            throw new RuntimeException('Stripe could not start the payment: '.($res->json('error.message') ?: 'unexpected response'));
        }

        return ['authorization_url' => $res->json('url'), 'access_code' => null, 'reference' => $tx->reference, 'gateway_reference' => $res->json('id')];
    }

    public function verify(string $reference): array
    {
        $tx = PaymentTransaction::query()->where('reference', $reference)->firstOrFail();
        $session = $tx->gateway_reference;
        if (! $session) {
            throw new RuntimeException('Stripe did not return a checkout session.');
        }
        $res = Http::withToken($this->secret())->acceptJson()->timeout(20)->get('https://api.stripe.com/v1/checkout/sessions/'.$session);
        if (! $res->successful()) {
            throw new RuntimeException('Stripe verification failed.');
        }
        $paid = $res->json('payment_status') === 'paid';

        return [
            'status' => $paid ? 'success' : 'pending',
            'amount' => (int) $res->json('amount_total'),
            'currency' => strtoupper((string) $res->json('currency')),
            'channel' => 'card',
            'raw' => $res->json(),
            'authorization' => null,
        ];
    }

    public function refund(PaymentTransaction $tx, int $amountKobo, string $reason): array
    {
        $intent = $tx->gateway_response['payment_intent'] ?? null;
        if (! $intent) {
            throw new RuntimeException('This Stripe payment has no payment intent yet. Refund it from the Stripe dashboard, then reject this request.');
        }
        $res = Http::withToken($this->secret())->asForm()->acceptJson()->post('https://api.stripe.com/v1/refunds', [
            'payment_intent' => $intent, 'amount' => $amountKobo,
        ]);
        if (! $res->successful()) {
            throw new RuntimeException('Stripe refund failed: '.($res->json('error.message') ?: 'unexpected response'));
        }

        return ['id' => $res->json('id'), 'status' => $res->json('status') ?: 'pending', 'raw' => $res->json()];
    }

    public function validWebhookSignature(string $payload, ?string $signature): bool
    {
        return false;
    }
}
