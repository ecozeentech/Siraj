<?php

namespace App\Services\Finance;

use App\Models\Finance\PaymentTransaction;

interface PaymentGateway
{
    public function name(): string;

    public function isConfigured(): bool;

    public function mode(): string;

    /** @return array{authorization_url: string, access_code: ?string, reference: string} */
    public function initialize(PaymentTransaction $tx, string $email, string $callbackUrl): array;

    /** @return array{status: string, amount: int, currency: string, channel: ?string, raw: array, authorization: ?array} status: success|failed|abandoned|pending */
    public function verify(string $reference): array;

    /** @return array{id: ?string, status: string, raw: array} */
    public function refund(PaymentTransaction $tx, int $amountKobo, string $reason): array;

    public function validWebhookSignature(string $payload, ?string $signature): bool;
}
