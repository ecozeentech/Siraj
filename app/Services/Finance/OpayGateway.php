<?php

namespace App\Services\Finance;

use App\Models\Finance\PaymentTransaction;
use App\Services\Settings;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpayGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'opay';
    }

    private function publicKey(): ?string
    {
        return Settings::get('gateway.opay.public_key');
    }

    private function merchant(): ?string
    {
        return Settings::get('gateway.opay.merchant_id');
    }

    public function isConfigured(): bool
    {
        return filled($this->publicKey()) && filled($this->merchant());
    }

    public function mode(): string
    {
        return Settings::get('gateway.opay.mode', 'sandbox') === 'live' ? 'live' : 'sandbox';
    }

    private function host(): string
    {
        return $this->mode() === 'live' ? 'https://api.opaycheckout.com' : 'https://sandboxapi.opaycheckout.com';
    }

    public function initialize(PaymentTransaction $tx, string $email, string $callbackUrl): array
    {
        $res = Http::withToken($this->publicKey())->withHeaders(['MerchantId' => $this->merchant()])->acceptJson()->timeout(20)
            ->post($this->host().'/api/v1/international/cashier/create', [
                'country' => 'NG',
                'reference' => $tx->reference,
                'amount' => ['total' => $tx->amount_kobo, 'currency' => $tx->currency],
                'returnUrl' => $callbackUrl.'?reference='.$tx->reference,
                'callbackUrl' => route('webhooks.opay'),
                'cancelUrl' => route('app.wallet.index'),
                'product' => ['name' => 'SIRAJ', 'description' => str_replace('_', ' ', $tx->purpose)],
                'userInfo' => ['userEmail' => $email],
            ]);
        $url = $res->json('data.cashierUrl');
        if (! $res->successful() || ! $url) {
            throw new RuntimeException('OPay could not start the payment: '.($res->json('message') ?: 'unexpected response'));
        }

        return ['authorization_url' => $url, 'access_code' => null, 'reference' => $tx->reference];
    }

    public function verify(string $reference): array
    {
        $res = Http::withToken($this->publicKey())->withHeaders(['MerchantId' => $this->merchant()])->acceptJson()->timeout(20)
            ->post($this->host().'/api/v1/international/cashier/status', ['reference' => $reference, 'country' => 'NG']);
        if (! $res->successful()) {
            throw new RuntimeException('OPay verification failed: '.($res->json('message') ?: $res->status()));
        }
        $data = $res->json('data') ?: [];
        $status = strtoupper((string) ($data['status'] ?? '')) === 'SUCCESS' ? 'success' : 'pending';

        return [
            'status' => $status,
            'amount' => (int) ($data['amount']['total'] ?? 0),
            'currency' => strtoupper((string) ($data['amount']['currency'] ?? 'NGN')),
            'channel' => 'opay',
            'raw' => $data,
            'authorization' => null,
        ];
    }

    public function refund(PaymentTransaction $tx, int $amountKobo, string $reason): array
    {
        throw new RuntimeException('Refund this OPay payment from the OPay dashboard, then record it as a wallet refund if the member should be credited inside SIRAJ.');
    }

    public function validWebhookSignature(string $payload, ?string $signature): bool
    {
        return false;
    }
}
