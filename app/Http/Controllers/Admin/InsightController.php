<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Events\EventRegistration;
use App\Models\Finance\PaymentTransaction;
use App\Models\System\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InsightController extends Controller
{
    public function reports()
    {
        return view('admin.insights.reports', [
            'members' => User::query()->where('is_staff', false)->count(),
            'newWeek' => User::query()->where('created_at', '>=', now()->subDays(7))->count(),
            'paid' => (int) PaymentTransaction::query()->where('status', 'succeeded')->sum('amount_kobo'),
            'byPurpose' => PaymentTransaction::query()->where('status', 'succeeded')->select('purpose', DB::raw('count(*) c'), DB::raw('sum(amount_kobo) amount'))->groupBy('purpose')->get(),
            'registrations' => EventRegistration::query()->where('status', 'registered')->count(),
        ]);
    }

    public function export(string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['members', 'payments', 'registrations'], true), 404);
        $filename = 'siraj-'.$type.'-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($type) {
            $out = fopen('php://output', 'w');
            if ($type === 'members') {
                fputcsv($out, ['id', 'name', 'email', 'status', 'joined']);
                User::query()->where('is_staff', false)->orderBy('id')->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $u) {
                        fputcsv($out, [$u->id, $u->name, $u->email, $u->status, $u->created_at]);
                    }
                });
            } elseif ($type === 'payments') {
                fputcsv($out, ['reference', 'email', 'purpose', 'amount_ngn', 'status', 'paid_at']);
                PaymentTransaction::query()->with('user')->orderBy('id')->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $p) {
                        fputcsv($out, [$p->reference, $p->user?->email, $p->purpose, number_format($p->amount_kobo / 100, 2, '.', ''), $p->status, $p->paid_at]);
                    }
                });
            } else {
                fputcsv($out, ['ticket', 'event', 'email', 'status', 'registered_at']);
                EventRegistration::query()->with(['event', 'user'])->orderBy('id')->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [$r->ticket_code, $r->event?->title, $r->user?->email, $r->status, $r->created_at]);
                    }
                });
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function audit(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $logs = AuditLog::query()->with('actor')->when($request->query('category'), fn ($w, $c) => $w->where('category', $c))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('action', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%")))
            ->latest('id')->paginate(30)->withQueryString();

        return view('admin.insights.audit', ['logs' => $logs, 'q' => $q]);
    }
}
