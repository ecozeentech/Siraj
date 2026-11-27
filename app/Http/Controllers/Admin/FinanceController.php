<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finance\PaymentProvider;
use App\Models\Finance\PaymentTransaction;
use App\Models\Finance\PointEntry;
use App\Models\Finance\ReferralAccount;
use App\Models\Finance\Refund;
use App\Models\Finance\Subscription;
use App\Models\Finance\Wallet;
use App\Models\User;
use App\Services\Admin\AuditLogger;
use App\Services\Finance\InsufficientFundsException;
use App\Services\Finance\LedgerService;
use App\Services\Finance\PaymentService;
use App\Services\Finance\RefundService;
use App\Services\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FinanceController extends Controller
{
    public function payments(Request $request)
    {
        return view('admin.finance.payments', [
            'payments' => PaymentTransaction::query()->with('user')->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))->latest()->paginate(20, ['*'], 'payments')->withQueryString(),
            'refunds' => Refund::query()->with('transaction.user')->latest()->paginate(15, ['*'], 'refunds'),
        ]);
    }

    public function refund(Request $request, Refund $refund, RefundService $refunds)
    {
        $data = $request->validate(['decision' => ['required', 'in:process,reject'], 'why' => ['nullable', 'string', 'max:500']]);
        $actor = $request->user('admin');
        try {
            if ($data['decision'] === 'process') {
                $refunds->process($refund, $actor);
                $message = 'Refund processed and recorded in the ledger.';
            } else {
                $refunds->reject($refund, $actor, $data['why'] ?: 'Declined');
                $message = 'Refund request declined.';
            }
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $message);
    }

    public function adjust(Request $request, LedgerService $ledger)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'direction' => ['required', Rule::in(['credit', 'debit'])],
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);
        $user = User::query()->where('email', strtolower($data['email']))->firstOrFail();
        $wallet = Wallet::query()->where('user_id', $user->id)->firstOrFail();
        $kobo = (int) round($data['amount'] * 100);
        $userLine = ['account' => $ledger->ensureUserAccount($wallet), 'direction' => $data['direction'], 'amount' => $kobo];
        $system = ['account' => 'system:adjustments', 'direction' => $data['direction'] === 'credit' ? 'debit' : 'credit', 'amount' => $kobo];
        try {
            $ledger->post([$system, $userLine], 'Adjustment: '.$data['reason'], 'adj:'.Str::uuid(), null);
        } catch (InsufficientFundsException) {
            return back()->with('error', 'That debit is larger than the wallet balance. Nothing was changed.');
        }
        AuditLogger::log('finance.adjusted', $wallet, $data['reason'], ['direction' => $data['direction'], 'amount_kobo' => $kobo, 'user_id' => $user->id]);

        return back()->with('status', 'Wallet '.($data['direction'] === 'credit' ? 'credited' : 'debited').' for '.$user->name.'.');
    }

    public function subscriptions()
    {
        return view('admin.finance.subscriptions', [
            'subscriptions' => Subscription::query()->with(['user', 'plan'])->latest()->paginate(20),
        ]);
    }

    public function gateways()
    {
        return view('admin.finance.gateways', ['providers' => PaymentProvider::query()->orderBy('sort')->orderBy('name')->get()]);
    }

    public function updateGateway(Request $request, PaymentProvider $provider)
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'public_details' => ['nullable', 'string', 'max:2000'],
            'secret' => ['nullable', 'string', 'max:500'],
            'public_key' => ['nullable', 'string', 'max:500'],
            'merchant_id' => ['nullable', 'string', 'max:120'],
        ]);
        $details = null;
        if (filled($data['public_details'] ?? null)) {
            $decoded = json_decode($data['public_details'], true);
            if (! is_array($decoded)) {
                return back()->withErrors(['public_details' => 'Public details must be a JSON object, for example {"bank":"Example Bank","account_number":"0123456789"}']);
            }
            $details = $decoded;
        }
        $provider->update([
            'enabled' => $request->boolean('enabled'),
            'instructions' => $data['instructions'] ?? $provider->instructions,
            'public_details' => $details ?? $provider->public_details,
        ]);
        foreach (['secret' => 'secret', 'public_key' => 'public_key', 'merchant_id' => 'merchant_id'] as $field => $suffix) {
            if (filled($data[$field] ?? null)) {
                Settings::set('gateway.'.$provider->slug.'.'.$suffix, $data[$field], 'payments', $field === 'secret');
            }
        }
        if ($provider->driver === 'paystack' && filled($data['secret'] ?? null)) {
            Settings::set('paystack.secret_key', $data['secret'], 'payments', true);
        }
        if ($provider->driver === 'paystack' && filled($data['public_key'] ?? null)) {
            Settings::set('paystack.public_key', $data['public_key'], 'payments');
        }
        AuditLogger::log('payment_provider.updated', $provider, $provider->name.' updated', ['enabled' => $provider->enabled]);

        return back()->with('status', $provider->name.' saved. Secrets are stored encrypted and are not shown again.');
    }

    public function storeGateway(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'instructions' => ['required', 'string', 'max:1000'],
        ]);
        $slug = Str::slug($data['name']);
        $slug = substr($slug, 0, 24) ?: 'custom';
        if (PaymentProvider::query()->where('slug', $slug)->exists()) {
            $slug = substr($slug, 0, 18).'-'.Str::lower(Str::random(4));
        }
        PaymentProvider::query()->create([
            'slug' => $slug, 'name' => $data['name'], 'driver' => 'custom', 'enabled' => true, 'sort' => 50,
            'instructions' => $data['instructions'],
        ]);

        return back()->with('status', 'Custom gateway added. Members can choose it, and a payment stays pending until you confirm it.');
    }

    public function confirmPayment(Request $request, PaymentTransaction $transaction, PaymentService $payments)
    {
        abort_unless($transaction->status === 'pending', 422);
        $provider = PaymentProvider::query()->where('slug', $transaction->gateway)->first();
        abort_unless($provider?->isManual(), 422, 'Only instruction-based payments can be confirmed here.');
        $payments->fulfil($transaction, [
            'status' => 'success', 'amount' => (int) $transaction->amount_kobo, 'currency' => $transaction->currency,
            'channel' => $transaction->gateway, 'raw' => ['confirmed_by' => $request->user('admin')->id], 'authorization' => null,
        ]);
        AuditLogger::log('payment.confirmed', $transaction, ['gateway' => $transaction->gateway]);

        return back()->with('status', 'Payment confirmed and recorded in the ledger.');
    }

    public function referrals()
    {
        return view('admin.finance.referrals', [
            'accounts' => ReferralAccount::query()->with('user')->orderByDesc('balance')->paginate(20),
            'entries' => PointEntry::query()->with('user')->latest()->limit(30)->get(),
            'enabled' => Settings::get('referrals.enabled', '1') !== '0',
            'points' => (int) Settings::get('referrals.points_per_signup', 100),
            'cost' => (int) Settings::get('referrals.points_to_redeem', 500),
            'planId' => Settings::get('referrals.plan_id'),
            'plans' => \App\Models\Finance\SubscriptionPlan::query()->where('is_active', true)->orderBy('sort')->get(),
        ]);
    }

    public function updateReferrals(Request $request)
    {
        $data = $request->validate([
            'points_per_signup' => ['required', 'integer', 'min:0', 'max:100000'],
            'points_to_redeem' => ['required', 'integer', 'min:1', 'max:1000000'],
            'plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
        ]);
        Settings::set('referrals.enabled', $request->boolean('enabled') ? '1' : '0', 'referrals');
        Settings::set('referrals.points_per_signup', (string) $data['points_per_signup'], 'referrals');
        Settings::set('referrals.points_to_redeem', (string) $data['points_to_redeem'], 'referrals');
        Settings::set('referrals.plan_id', (string) ($data['plan_id'] ?? ''), 'referrals');

        return back()->with('status', 'Referral rewards saved.');
    }
}
