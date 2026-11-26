<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ai\AiUsageLog;
use App\Models\Finance\SubscriptionPlan;
use App\Models\NotificationPreference;
use App\Models\Role;
use App\Models\System\BroadcastNotification;
use App\Services\Admin\AuditLogger;
use App\Services\Admin\BroadcastService;
use App\Services\Ai\AiService;
use App\Services\Push\WebPushService;
use App\Services\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PlatformController extends Controller
{
    public function ai()
    {
        return view('admin.platform.ai', [
            'byStatus' => AiUsageLog::query()->select('status', DB::raw('count(*) as c'), DB::raw('sum(tokens_in) as tin'), DB::raw('sum(tokens_out) as tout'), DB::raw('sum(cost_usd) as cost'))->groupBy('status')->get(),
            'recent' => AiUsageLog::query()->latest('id')->limit(15)->get(),
            'connected' => app(AiService::class)->activeProvider()[0] !== null,
        ]);
    }

    public function notifications(BroadcastService $broadcasts)
    {
        return view('admin.platform.notifications', [
            'broadcasts' => BroadcastNotification::query()->latest()->paginate(15),
            'plans' => SubscriptionPlan::query()->orderBy('sort')->get(), 'roles' => Role::query()->orderBy('name')->get(),
            'pushReady' => app(WebPushService::class)->configured(),
        ]);
    }

    public function preview(Request $request, BroadcastService $broadcasts)
    {
        $data = $this->broadcastData($request);
        $draft = new BroadcastNotification($data);

        return back()->withInput()->with('preview', $broadcasts->audienceCount($draft));
    }

    public function storeBroadcast(Request $request, BroadcastService $broadcasts)
    {
        $data = $this->broadcastData($request);
        $data['created_by'] = Auth::guard('admin')->id();
        $data['status'] = $request->input('when') === 'schedule' ? 'scheduled' : 'draft';
        $broadcast = BroadcastNotification::query()->create($data);
        AuditLogger::log('notifications.created', $broadcast, 'Created broadcast '.$broadcast->title);
        if ($request->input('when') === 'now') {
            $broadcasts->send($broadcast->fresh());

            return redirect()->route('admin.notifications.index')->with('status', 'Broadcast sent to '.$broadcast->fresh()->recipients_count.' members.');
        }

        return redirect()->route('admin.notifications.index')->with('status', $data['status'] === 'scheduled' ? 'Broadcast scheduled.' : 'Draft saved.');
    }

    public function settings(WebPushService $push)
    {
        return view('admin.platform.settings', [
            'requireVerified' => Settings::has('auth.require_verified_email') ? filter_var(Settings::get('auth.require_verified_email'), FILTER_VALIDATE_BOOLEAN) : (bool) config('siraj.require_verified_email'),
            'supportEmail' => Settings::get('support.email', 'hello@sirajmuslim.com'),
            'paystackPublic' => Settings::has('paystack.public_key'), 'paystackSecret' => Settings::has('paystack.secret_key'),
            'pushPublic' => $push->configured(), 'pushSubject' => Settings::get('push.vapid_subject', config('services.vapid.subject')),
        ]);
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'support_email' => ['required', 'email', 'max:255'],
            'require_verified_email' => ['nullable', 'boolean'],
            'paystack_public_key' => ['nullable', 'string', 'max:255'],
            'paystack_secret_key' => ['nullable', 'string', 'max:255'],
            'vapid_public_key' => ['nullable', 'string', 'max:255'],
            'vapid_private_key' => ['nullable', 'string', 'max:500'],
            'vapid_subject' => ['nullable', 'string', 'max:255'],
        ]);
        Settings::set('support.email', $data['support_email'], 'general');
        Settings::set('auth.require_verified_email', $request->boolean('require_verified_email') ? '1' : '0', 'auth');
        foreach (['paystack.public_key' => 'paystack_public_key', 'paystack.secret_key' => 'paystack_secret_key', 'push.vapid_public' => 'vapid_public_key', 'push.vapid_private' => 'vapid_private_key'] as $key => $input) {
            if (filled($data[$input] ?? null)) {
                Settings::set($key, $data[$input], str_starts_with($key, 'push') ? 'push' : 'payments', str_contains($key, 'secret') || str_contains($key, 'private'));
            }
        }
        if (filled($data['vapid_subject'] ?? null)) {
            Settings::set('push.vapid_subject', $data['vapid_subject'], 'push');
        }
        AuditLogger::log('settings.updated', null, 'Application settings updated', ['keys' => array_keys(array_filter($data))]);

        return back()->with('status', 'Settings saved. Secret values are stored encrypted and are not shown again.');
    }

    private function broadcastData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'], 'body' => ['required', 'string', 'max:2000'],
            'category' => ['required', Rule::in(array_keys(NotificationPreference::CATEGORIES))],
            'url' => ['nullable', 'string', 'max:255'], 'audience' => ['required', Rule::in(['all', 'plan', 'role'])],
            'plan_id' => ['nullable', 'integer'], 'role_id' => ['nullable', 'integer'],
            'channels' => ['required', 'array', 'min:1'], 'channels.*' => [Rule::in(['in_app', 'email', 'push'])],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ]);
        $filter = match ($data['audience']) {
            'plan' => ['plan_ids' => array_filter([(int) ($data['plan_id'] ?? 0)])],
            'role' => ['role_ids' => array_filter([(int) ($data['role_id'] ?? 0)])],
            default => null,
        };

        return ['title' => $data['title'], 'body' => $data['body'], 'category' => $data['category'], 'url' => $data['url'] ?: null, 'audience' => $data['audience'], 'audience_filter' => $filter, 'channels' => array_values($data['channels']), 'scheduled_at' => $data['scheduled_at'] ?? null];
    }
}
