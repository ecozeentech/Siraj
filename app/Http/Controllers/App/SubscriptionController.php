<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Finance\SubscriptionPlan;
use App\Services\Finance\GatewayNotConfiguredException;
use App\Services\Finance\InsufficientFundsException;
use App\Services\Finance\PaymentGateway;
use App\Services\Finance\PaymentService;
use App\Services\Finance\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request, PaymentGateway $gateway)
    {
        $user = $request->user();

        return view('app.subscription', [
            'plans' => SubscriptionPlan::query()->where('is_active', true)->orderBy('sort')->get(), 'current' => $user->activeSubscription?->load('plan'),
            'balance' => (int) $user->wallet?->balance_kobo, 'gatewayReady' => $gateway->isConfigured(),
        ]);
    }

    public function subscribe(Request $request, SubscriptionPlan $plan, SubscriptionService $subs, PaymentService $payments)
    {
        abort_unless($plan->is_active, 404);
        $data = $request->validate(['method' => ['nullable', 'in:wallet,gateway']]);
        $user = $request->user();
        if ($user->activeSubscription?->subscription_plan_id === $plan->id && $plan->interval !== 'free') {
            return back()->withErrors(['method' => 'You are already on this plan.']);
        }

        try {
            if ($plan->price_kobo <= 0) {
                $subs->activate($user, $plan, null, 'admin');

                return redirect()->route('app.subscription.index')->with('status', 'You are now on the '.$plan->name.' plan.');
            }
            if (($data['method'] ?? 'gateway') === 'wallet') {
                $subs->subscribeWithWallet($user, $plan);

                return redirect()->route('app.subscription.index')->with('status', 'Welcome to '.$plan->name.'. Your plan is active.');
            }
            $tx = $payments->initiate($user, 'subscription', (int) $plan->price_kobo, $plan, ['plan' => $plan->slug]);

            return redirect()->away($tx->authorization_url);
        } catch (InsufficientFundsException) {
            return back()->withErrors(['method' => 'Your wallet balance is not enough. Top up your wallet or pay online.']);
        } catch (GatewayNotConfiguredException $e) {
            return back()->withErrors(['method' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['method' => 'We could not complete that. Please try again.']);
        }
    }

    public function cancel(Request $request, SubscriptionService $subs)
    {
        $sub = $request->user()->activeSubscription;
        abort_unless($sub, 404);
        $subs->cancel($sub);

        return back()->with('status', 'Your plan will not renew. You keep access until '.($sub->ends_at?->format('j M Y') ?? 'it is ended by an administrator').'.');
    }
}
