<?php

namespace App\Services\Finance;

use App\Models\Finance\PaymentProvider;
use App\Models\Finance\PaymentTransaction;

/** Instructions-only gateway. A payment stays pending until a staff member confirms the money arrived. */
class ManualGateway implements PaymentGateway
{
    public function __construct(private PaymentProvider $provider) {}

    public function name(): string
    {
        return $this->provider->slug;
    }

    public function isConfigured(): bool
    {
        return $this->provider->enabled;
    }

    public function mode(): string
    {
        return 'live';
    }

    public function initialize(PaymentTransaction $tx, string $email, string $callbackUrl): array
    {
        return [
            'authorization_url' => route('app.payments.instructions', $tx),
            'access_code' => null,
            'reference' => $tx->reference,
        ];
    }

    public function verify(string $reference): array
    {
        return ['status' => 'pending', 'amount' => 0, 'currency' => 'NGN', 'channel' => $this->provider->slug, 'raw' => [], 'authorization' => null];
    }

    public function refund(PaymentTransaction $tx, int $amountKobo, string $reason): array
    {
        return ['id' => null, 'status' => 'manual', 'raw' => ['note' => 'Recorded after the refund was sent outside SIRAJ.']];
    }

    public function validWebhookSignature(string $payload, ?string $signature): bool
    {
        return false;
    }
}
