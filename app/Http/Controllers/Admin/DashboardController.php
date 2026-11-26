<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community\ContentReport;
use App\Models\Directory\Business;
use App\Models\Directory\DirectoryReport;
use App\Models\Finance\PaymentTransaction;
use App\Models\Finance\Refund;
use App\Models\Quran\QuranEdition;
use App\Models\System\AuditLog;
use App\Models\System\SupportTicket;
use App\Models\User;
use App\Services\Finance\LedgerService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $edition = QuranEdition::query()->where('slug', 'quran-uthmani')->first();

        return view('admin.dashboard', [
            'stats' => [
                ['Members', User::query()->where('is_staff', false)->where('status', 'active')->count(), 'users'],
                ['Active plans', DB::table('subscriptions')->where('status', 'active')->where('subscription_plan_id', '!=', DB::table('subscription_plans')->where('price_kobo', 0)->value('id'))->count(), 'crown'],
                ['Open reports', ContentReport::query()->where('status', 'open')->count(), 'shield-alert'],
                ['Open tickets', SupportTicket::query()->whereIn('status', ['open', 'pending'])->count(), 'life-buoy'],
                ['Listings to review', Business::query()->where('status', 'pending')->count() + DirectoryReport::query()->where('status', 'open')->count(), 'map-pin'],
                ['Refunds waiting', Refund::query()->where('status', 'requested')->count(), 'undo-2'],
            ],
            'paidToday' => (int) PaymentTransaction::query()->where('status', 'succeeded')->whereDate('paid_at', today())->sum('amount_kobo'),
            'quran' => $edition,
            'audit' => AuditLog::query()->with('actor')->latest('id')->limit(8)->get(),
            'revenue' => app(LedgerService::class)->balanceOf(app(LedgerService::class)->systemAccount('system:subscription_revenue'))
                + app(LedgerService::class)->balanceOf(app(LedgerService::class)->systemAccount('system:event_revenue')),
        ]);
    }
}
