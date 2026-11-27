<?php

namespace App\Services\Finance;

use App\Models\Finance\PaymentTransaction;
use App\Services\Settings;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FlutterwaveGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'flutterwave';
    }

    private function secret(): ?string
    {
        return Settings::get('gateway.flutterwave.secret');
    }

    public function isConfigured(): bool
    {
        return filled($this->secret());
    }

    public function mode(): string
    {
        return str_contains((string) $this->secret(), 'TEST') ? 'sandbox' : 'live';
    }

    public function initialize(PaymentTransaction $tx, string $email, string $callbackUrl): array
    {
        $res = Http::withToken($this->secret())->acceptJson()->timeout(20)->post('https://api.flutterwave.com/v3/payments', [
            'tx_ref' => $tx->reference,
            'amount' => $tx->amount_kobo / 100,
            'currency' => $tx->currency,
            'redirect_url' => $callbackUrl.'?reference='.$tx->reference,
            'customer' => ['email' => $email],
            'customizations' => ['title' => 'SIRAJ', 'description' => str_replace('_', ' ', $tx->purpose)],
        ]);
        $link = $res->json('data.link');
        if (! $res->successful() || ! $link) {
            throw new RuntimeException('Flutterwave could not start the payment: '.($res->json('message') ?: 'unexpected response'));
        }

        return ['authorization_url' => $link, 'access_code' => null, 'reference' => $tx->reference];
    }

    public function verify(string $reference): array
    {
        $res = Http::withToken($this->secret())->acceptJson()->timeout(20)->get('https://api.flutterwave.com/v3/transactions/verify_by_reference', ['tx_ref' => $reference]);
        if (! $res->successful()) {
            throw new RuntimeException('Flutterwave verification failed: '.($res->json('message') ?: $res->status()));
        }
        $data = $res->json('data') ?: [];
        $status = ($data['status'] ?? '') === 'successful' ? 'success' : 'pending';

        return [
            'status' => $status,
            'amount' => (int) round(((float) ($data['amount'] ?? 0)) * 100),
            'currency' => strtoupper((string) ($data['currency'] ?? 'NGN')),
            'channel' => $data['payment_type'] ?? null,
            'raw' => $data,
            'authorization' => null,
        ];
    }

    public function refund(PaymentTransaction $tx, int $amountKobo, string $reason): array
    {
        $id = $tx->gateway_response['id'] ?? null;
        if (! $id) {
            throw new RuntimeException('This Flutterwave payment has no transaction id. Refund it from the Flutterwave dashboard.');
        }
        $res = Http::withToken($this->secret())->acceptJson()->post('https://api.flutterwave.com/v3/transactions/'.$id.'/refund', ['amount' => $amountKobo / 100]);
        if (! $res->successful()) {
            throw new RuntimeException('Flutterwave refund failed: '.($res->json('message') ?: 'unexpected response'));
        }

        return ['id' => (string) $res->json('data.id'), 'status' => $res->json('data.status') ?: 'pending', 'raw' => $res->json()];
    }

    public function validWebhookSignature(string $payload, ?string $signature): bool
    {
        $secret = Settings::get('gateway.flutterwave.secret_hash');

        return filled($secret) && filled($signature) && hash_equals((string) $secret, (string) $signature);
    }
}
