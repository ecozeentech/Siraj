<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Finance\PointEntry;
use App\Services\Finance\ReferralService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ReferralController extends Controller
{
    public function index(Request $request, ReferralService $referrals)
    {
        $user = $request->user();
        $account = $referrals->account($user);

        return view('app.referrals', [
            'account' => $account,
            'entries' => PointEntry::query()->where('user_id', $user->id)->latest()->limit(20)->get(),
            'enabled' => $referrals->enabled(),
            'cost' => $referrals->pointsToRedeem(),
            'plan' => $referrals->redeemPlan(),
            'link' => route('register', ['ref' => $account->code]),
        ]);
    }

    public function redeem(Request $request, ReferralService $referrals)
    {
        try {
            $referrals->redeem($request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['redeem' => $e->getMessage()]);
        }

        return redirect()->route('app.subscription.index')->with('status', 'Your points have been redeemed and the plan is active.');
    }
}
