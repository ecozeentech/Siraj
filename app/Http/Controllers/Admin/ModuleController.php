<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Registry;
use App\Http\Controllers\Controller;
use App\Models\Directory\DirectoryReport;
use App\Models\Events\Event;
use App\Models\Events\EventRegistration;
use App\Models\Quran\QuranEdition;
use App\Services\Admin\AuditLogger;
use App\Services\Quran\QuranVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
    public function home(Request $request)
    {
        $module = $request->route('module');
        abort_unless(isset(Registry::MODULES[$module]), 404);
        $resources = Registry::forModule($module);

        return view('admin.module', [
            'module' => $module, 'meta' => Registry::MODULES[$module], 'resources' => $resources,
            'counts' => collect($resources)->map(fn ($r) => $r['model']::query()->count()),
            'edition' => $module === 'quran' ? QuranEdition::query()->where('slug', 'quran-uthmani')->first() : null,
            'reports' => $module === 'directories' ? DirectoryReport::query()->with('reporter')->where('status', 'open')->latest()->limit(20)->get() : collect(),
            'events' => $module === 'events' ? Event::query()->orderByDesc('starts_at')->limit(30)->get(['id', 'title', 'starts_at']) : collect(),
        ]);
    }

    public function verifyQuran(QuranVerifier $verifier)
    {
        $report = $verifier->run(Auth::guard('admin')->id());
        AuditLogger::log('quran.verified', null, $report['passed'] ? 'Quran verification passed' : 'Quran verification found problems', ['passed' => $report['passed']]);

        return back()->with($report['passed'] ? 'status' : 'error', $report['passed'] ? 'Verification passed. All checks matched the imported text.' : 'Verification found problems. The stored text was not changed.');
    }

    public function attend(Request $request)
    {
        $data = $request->validate(['event_id' => ['required', 'exists:events,id'], 'ticket_code' => ['required', 'string', 'max:20']]);
        $reg = EventRegistration::query()->where('event_id', $data['event_id'])->where('ticket_code', strtoupper(trim($data['ticket_code'])))->first();
        if (! $reg || ! in_array($reg->status, ['registered', 'attended'], true)) {
            return back()->with('error', 'No active ticket matches that code for the selected event.');
        }
        $reg->update(['status' => 'attended', 'attended_at' => $reg->attended_at ?? now()]);
        AuditLogger::log('events.attended', $reg, 'Marked ticket '.$reg->ticket_code.' attended');

        return back()->with('status', 'Ticket '.$reg->ticket_code.' is marked attended.');
    }

    public function directoryReport(Request $request, DirectoryReport $report)
    {
        $data = $request->validate(['decision' => ['required', 'in:applied,dismissed']]);
        abort_unless($report->status === 'open', 422);
        $report->update(['status' => $data['decision'], 'reviewed_by' => Auth::guard('admin')->id(), 'reviewed_at' => now()]);
        AuditLogger::log('directory.report.'.$data['decision'], $report, 'Directory report '.$data['decision']);

        return back()->with('status', 'Report marked '.$data['decision'].'. Edit the listing itself if a correction is needed.');
    }
}
