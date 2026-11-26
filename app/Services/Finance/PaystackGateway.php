<?php

namespace App\Services\Finance;

use App\Models\Finance\PaymentTransaction;
use App\Services\Settings;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Paystack integration (https://paystack.com/docs/api). Keys come from Admin > Settings (encrypted) or env.
 * Paystack test keys (sk_test_…) run in sandbox; live keys (sk_live_…) run in live mode. We never fabricate
 * a successful response: if the key is missing, isConfigured() is false and callers must surface that.
 */
class PaystackGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'paystack';
    }

    private function secret(): ?string
    {
        return Settings::get('paystack.secret_key') ?: config('services.paystack.secret_key');
    }

    public function publicKey(): ?string
    {
        return Settings::get('paystack.public_key') ?: config('services.paystack.public_key');
    }

    public function isConfigured(): bool
    {
        return filled($this->secret());
    }

    public function mode(): string
    {
        $key = (string) $this->secret();

        return str_starts_with($key, 'sk_live_') ? 'live' : 'sandbox';
    }

    private function http(): PendingRequest
    {
        if (! $this->isConfigured()) {
            throw new GatewayNotConfiguredException('Paystack is not configured. Add API keys in Admin > Settings > Payments.');
        }

        return Http::withToken($this->secret())->baseUrl(config('services.paystack.base_url'))->acceptJson()->timeout(20)->retry(2, 300, throw: false);
    }

    public function initialize(PaymentTransaction $tx, string $email, string $callbackUrl): array
    {
        $res = $this->http()->post('/transaction/initialize', [
            'email' => $email,
            'amount' => $tx->amount_kobo,
            'currency' => $tx->currency,
            'reference' => $tx->reference,
            'callback_url' => $callbackUrl,
            'metadata' => ['purpose' => $tx->purpose, 'user_id' => $tx->user_id],
        ]);
        if (! $res->successful() || ! $res->json('status')) {
            throw new RuntimeException('Paystack could not start the payment: '.($res->json('message') ?: 'unexpected response'));
        }

        return [
            'authorization_url' => $res->json('data.authorization_url'),
            'access_code' => $res->json('data.access_code'),
            'reference' => $res->json('data.reference') ?: $tx->reference,
        ];
    }

    public function verify(string $reference): array
    {
        $res = $this->http()->get('/transaction/verify/'.rawurlencode($reference));
        if (! $res->successful()) {
            throw new RuntimeException('Paystack verification failed: '.($res->json('message') ?: $res->status()));
        }
        $data = $res->json('data') ?? [];
        $status = match ($data['status'] ?? null) {
            'success' => 'success',
            'failed' => 'failed',
            'abandoned' => 'abandoned',
            default => 'pending',
        };

        return [
            'status' => $status,
            'amount' => (int) ($data['amount'] ?? 0),
            'currency' => (string) ($data['currency'] ?? ''),
            'channel' => $data['channel'] ?? null,
            'raw' => $data,
            'authorization' => ($data['authorization']['reusable'] ?? false) ? $data['authorization'] : null,
        ];
    }

    public function refund(PaymentTransaction $tx, int $amountKobo, string $reason): array
    {
        $res = $this->http()->post('/refund', ['transaction' => $tx->gateway_reference ?: $tx->reference, 'amount' => $amountKobo, 'merchant_note' => $reason]);
        if (! $res->successful() || ! $res->json('status')) {
            throw new RuntimeException('Paystack refund failed: '.($res->json('message') ?: $res->status()));
        }

        return ['id' => (string) $res->json('data.id'), 'status' => (string) $res->json('data.status'), 'raw' => $res->json('data') ?? []];
    }

    public function validWebhookSignature(string $payload, ?string $signature): bool
    {
        if (! $this->isConfigured() || ! $signature) {
            return false;
        }

        return hash_equals(hash_hmac('sha512', $payload, $this->secret()), $signature);
    }
}
