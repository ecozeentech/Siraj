<?php

namespace App\Services\Finance;

use App\Models\Finance\PaymentProvider;
use Illuminate\Support\Collection;

class GatewayCatalog
{
    public function available(): Collection
    {
        return PaymentProvider::query()->orderBy('sort')->orderBy('name')->get();
    }

    public function checkoutOptions(): Collection
    {
        return $this->available()->filter(function (PaymentProvider $provider) {
            if (! $provider->enabled) {
                return false;
            }

            return $this->make($provider->slug)->isConfigured();
        })->values();
    }

    public function find(string $slug): PaymentProvider
    {
        return PaymentProvider::query()->where('slug', $slug)->firstOrFail();
    }

    public function make(string $slug): PaymentGateway
    {
        $provider = PaymentProvider::query()->where('slug', $slug)->first();
        if (! $provider || $provider->driver === 'paystack') {
            return app(PaystackGateway::class);
        }

        return match ($provider->driver) {
            'stripe' => app(StripeGateway::class),
            'flutterwave' => app(FlutterwaveGateway::class),
            'opay' => app(OpayGateway::class),
            default => new ManualGateway($provider),
        };
    }
}
