<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ai\AiReport;
use App\Models\Community\Comment;
use App\Models\Community\ContentReport;
use App\Models\Community\ModerationAction;
use App\Models\Community\ModerationAppeal;
use App\Models\Community\Post;
use App\Models\Community\UserRestriction;
use App\Models\Messaging\Message;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ModerationController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'appeals' ? 'appeals' : 'reports';

        return view('admin.moderation.index', [
            'tab' => $tab,
            'reports' => ContentReport::query()->with(['reporter', 'reportedUser'])->latest()->paginate(20, ['*'], 'reports'),
            'appeals' => ModerationAppeal::query()->with(['user', 'restriction'])->latest()->paginate(20, ['*'], 'appeals'),
            'aiReports' => AiReport::query()->with('user')->where('status', 'open')->latest()->limit(20)->get(),
        ]);
    }

    public function report(Request $request, ContentReport $report)
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['dismiss', 'hide', 'remove', 'restore', 'restrict', 'warn'])],
            'reason' => ['nullable', 'string', 'max:500'],
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'restriction' => ['nullable', Rule::in(['posting', 'messaging', 'all'])],
        ]);
        $actor = Auth::guard('admin')->id();
        $subject = $report->reportable;
        $reason = $data['reason'] ?: 'Moderation action';
        if (in_array($data['decision'], ['hide', 'remove', 'restore'], true) && ! $subject instanceof Post && ! $subject instanceof Comment && ! $subject instanceof Message) {
            return back()->with('error', 'Hiding, removing and restoring apply to posts, comments and messages.');
        }

        match ($data['decision']) {
            'dismiss' => $report->update(['status' => 'dismissed', 'reviewed_by' => $actor, 'reviewed_at' => now(), 'action_taken' => 'dismiss']),
            'hide' => $this->setStatus($subject, 'hidden', $report, $actor, 'hide'),
            'remove' => $this->setStatus($subject, 'removed', $report, $actor, 'remove'),
            'restore' => $this->setStatus($subject, 'published', $report, $actor, 'restore'),
            'restrict' => $this->restrict($report, $data['restriction'] ?? 'posting', $reason, $data['days'] ?? null, $actor),
            'warn' => $this->warn($report, $reason, $actor),
        };
        ModerationAction::query()->create(['moderator_id' => $actor, 'target_user_id' => $report->reported_user_id, 'subject_type' => $report->reportable_type, 'subject_id' => $report->reportable_id, 'action' => $data['decision'], 'reason' => $reason]);
        AuditLogger::log('moderation.'.$data['decision'], $report, $reason);

        return back()->with('status', 'Moderation action recorded.');
    }

    public function lift(UserRestriction $restriction)
    {
        $restriction->update(['lifted_at' => now()]);
        AuditLogger::log('moderation.lifted', $restriction, 'Restriction lifted');

        return back()->with('status', 'Restriction lifted.');
    }

    public function appeal(Request $request, ModerationAppeal $appeal)
    {
        $data = $request->validate(['decision' => ['required', 'in:upheld,overturned'], 'response' => ['required', 'string', 'max:2000']]);
        abort_unless($appeal->status === 'open', 422);
        if ($data['decision'] === 'overturned' && $appeal->restriction) {
            $appeal->restriction->update(['lifted_at' => now()]);
        }
        $appeal->update(['status' => $data['decision'], 'response' => $data['response'], 'resolved_by' => Auth::guard('admin')->id()]);
        $appeal->user->notify(new GeneralNotification('Your appeal was reviewed', $data['response'], '/app/community', 'community', 'shield'));
        AuditLogger::log('moderation.appeal.'.$data['decision'], $appeal, $data['response']);

        return back()->with('status', 'Appeal '.$data['decision'].'.');
    }

    public function aiReport(Request $request, AiReport $report)
    {
        $data = $request->validate(['status' => ['required', 'in:reviewed,dismissed']]);
        $report->update(['status' => $data['status'], 'resolved_by' => Auth::guard('admin')->id()]);

        return back()->with('status', 'AI report updated.');
    }

    private function setStatus(mixed $subject, string $status, ContentReport $report, int $actor, string $action): void
    {
        if ($subject instanceof Post || $subject instanceof Comment) {
            $subject->update(['status' => $status]);
            if ($status === 'removed') {
                $subject->delete();
            }
        } elseif ($subject instanceof Message && $status === 'removed') {
            $subject->delete();
        }
        $report->update(['status' => 'actioned', 'reviewed_by' => $actor, 'reviewed_at' => now(), 'action_taken' => $action]);
    }

    private function restrict(ContentReport $report, string $type, string $reason, ?int $days, int $actor): void
    {
        abort_unless($report->reported_user_id, 422);
        UserRestriction::query()->create(['user_id' => $report->reported_user_id, 'type' => $type, 'reason' => $reason, 'expires_at' => $days ? now()->addDays($days) : null, 'created_by' => $actor]);
        User::query()->find($report->reported_user_id)?->notify(new GeneralNotification('Community restriction', $reason, '/app/community', 'community', 'shield-alert'));
        $report->update(['status' => 'actioned', 'reviewed_by' => $actor, 'reviewed_at' => now(), 'action_taken' => 'restrict']);
    }

    private function warn(ContentReport $report, string $reason, int $actor): void
    {
        User::query()->find($report->reported_user_id)?->notify(new GeneralNotification('A note from SIRAJ moderators', $reason, '/app/community', 'community', 'triangle-alert'));
        $report->update(['status' => 'actioned', 'reviewed_by' => $actor, 'reviewed_at' => now(), 'action_taken' => 'warn']);
    }
}
