<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use App\Models\Quran\Reciter;
use App\Models\Quran\TafsirSource;
use App\Models\Quran\TranslationSource;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        return view('app.settings.index', ['user' => $request->user()]);
    }

    public function account(Request $request)
    {
        return view('app.settings.account', ['user' => $request->user()->load('profile')]);
    }

    public function updateAccount(Request $request)
    {
        $user = $request->user();
        $d = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:32', 'regex:/^\+?[0-9\s\-]{7,20}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'username' => ['nullable', 'string', 'min:3', 'max:40', 'regex:/^[a-z0-9_.]+$/i', Rule::unique('user_profiles', 'username')->ignore($user->profile->id ?? null)],
            'bio' => ['nullable', 'string', 'max:280'],
            'country' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:120'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
        ]);

        $emailChanged = strtolower($d['email']) !== $user->email;
        $user->fill(['name' => $d['name'], 'email' => strtolower($d['email']), 'phone' => $d['phone'] ?? null]);
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }
        $user->save();
        $user->profile()->updateOrCreate([], ['username' => $d['username'] ?: null, 'bio' => $d['bio'] ?? null, 'country' => $d['country'] ?? null, 'city' => $d['city'] ?? null]);

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('status', $emailChanged ? 'Profile saved. Please verify your new email address.' : 'Profile saved.');
    }

    public function password()
    {
        return view('app.settings.password');
    }

    public function updatePassword(Request $request)
    {
        $d = $request->validate(['current_password' => ['required', 'current_password'], 'password' => ['required', 'confirmed', Password::defaults(), 'different:current_password']]);
        $request->user()->update(['password' => $d['password']]);
        $this->endOtherSessions($request);
        AuditLogger::security('password.changed', $request->user()->id, 'Password changed');

        return back()->with('status', 'Password updated. Other devices were signed out.');
    }

    public function security(Request $request)
    {
        $sessions = DB::table('sessions')->where('user_id', $request->user()->id)->orderByDesc('last_activity')->get()->map(fn ($s) => (object) [
            'id' => $s->id, 'ip' => $s->ip_address, 'agent' => $this->describeAgent((string) $s->user_agent),
            'last' => \Carbon\Carbon::createFromTimestamp($s->last_activity), 'current' => $s->id === $request->session()->getId(),
        ]);

        return view('app.settings.security', ['sessions' => $sessions]);
    }

    public function endSessions(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $this->endOtherSessions($request);

        return back()->with('status', 'Signed out of all other devices.');
    }

    private function endOtherSessions(Request $request): void
    {
        DB::table('sessions')->where('user_id', $request->user()->id)->where('id', '!=', $request->session()->getId())->delete();
    }

    private function describeAgent(string $ua): string
    {
        $os = match (true) {
            str_contains($ua, 'Android') => 'Android', str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS', str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS') => 'macOS', str_contains($ua, 'Linux') => 'Linux', default => 'Unknown device',
        };
        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge', str_contains($ua, 'OPR/') => 'Opera', str_contains($ua, 'Firefox') => 'Firefox',
            str_contains($ua, 'Chrome') => 'Chrome', str_contains($ua, 'Safari') => 'Safari', default => 'Browser',
        };

        return "$browser on $os";
    }

    public function notifications(Request $request)
    {
        $prefs = NotificationPreference::query()->where('user_id', $request->user()->id)->get()->keyBy('category');

        return view('app.settings.notifications', ['prefs' => $prefs, 'categories' => NotificationPreference::CATEGORIES, 'pushReady' => filled(\App\Services\Settings::get('vapid.public_key') ?: config('services.vapid.public_key'))]);
    }

    public function updateNotifications(Request $request)
    {
        foreach (array_keys(NotificationPreference::CATEGORIES) as $cat) {
            $in = $request->input("prefs.$cat", []);
            NotificationPreference::query()->updateOrCreate(
                ['user_id' => $request->user()->id, 'category' => $cat],
                ['in_app' => $cat === 'system' ? true : isset($in['in_app']), 'email' => isset($in['email']), 'push' => isset($in['push'])],
            );
        }

        return back()->with('status', 'Notification preferences saved.');
    }

    public function appearance(Request $request)
    {
        return view('app.settings.appearance', ['prefs' => $request->user()->preferences]);
    }

    public function updateAppearance(Request $request)
    {
        $d = $request->validate(['theme' => ['required', Rule::in(['light', 'dark', 'system'])], 'language' => ['required', Rule::in(array_keys(config('siraj.supported_locales')))]]);
        $request->user()->preferences()->updateOrCreate(['user_id' => $request->user()->id], $d);
        $request->session()->put('locale', $d['language']);

        return back()->with('status', 'Appearance saved.');
    }

    public function theme(Request $request)
    {
        $theme = $request->validate(['theme' => ['required', Rule::in(['light', 'dark', 'system'])]])['theme'];
        $request->user()->preferences()->updateOrCreate(['user_id' => $request->user()->id], ['theme' => $theme]);

        return back();
    }

    public function quran(Request $request)
    {
        return view('app.settings.quran', [
            'prefs' => $request->user()->preferences,
            'translations' => TranslationSource::query()->where('is_active', true)->orderBy('sort')->get(),
            'tafsirs' => TafsirSource::query()->where('is_active', true)->orderBy('sort')->get(),
            'reciters' => Reciter::query()->where('is_active', true)->orderBy('sort')->get(),
        ]);
    }

    public function updateQuran(Request $request)
    {
        $d = $request->validate([
            'translation_source_id' => ['nullable', 'exists:translation_sources,id'], 'tafsir_source_id' => ['nullable', 'exists:tafsir_sources,id'],
            'reciter_id' => ['nullable', 'exists:reciters,id'], 'quran_font_size' => ['required', 'integer', 'between:22,56'],
            'quran_reading_mode' => ['required', Rule::in(['ayah', 'continuous', 'page'])], 'show_translation' => ['boolean'], 'show_tafsir' => ['boolean'],
        ]);
        $request->user()->preferences()->updateOrCreate([], $d);

        return back()->with('status', 'Quran preferences saved.');
    }

    public function privacy(Request $request)
    {
        return view('app.settings.privacy', ['profile' => $request->user()->profile, 'blocked' => $request->user()->blockedUsers()->get()]);
    }

    public function updatePrivacy(Request $request)
    {
        $d = $request->validate([
            'profile_visibility' => ['required', Rule::in(['public', 'followers', 'private'])], 'message_privacy' => ['required', Rule::in(['everyone', 'followers', 'nobody'])],
            'show_location' => ['boolean'], 'share_prayer_activity' => ['boolean'],
        ]);
        $request->user()->profile()->updateOrCreate([], $d);

        return back()->with('status', 'Privacy settings saved.');
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $data = [
            'exported_at' => now()->toIso8601String(),
            'account' => $user->only(['name', 'email', 'phone', 'created_at']),
            'profile' => $user->profile?->toArray(), 'preferences' => $user->preferences?->toArray(), 'prayer_preferences' => $user->prayerPreference?->toArray(),
            'bookmarks' => DB::table('bookmarks')->where('user_id', $user->id)->get(), 'notes' => DB::table('ayah_notes')->where('user_id', $user->id)->get(),
            'reading_history' => DB::table('reading_history')->where('user_id', $user->id)->get(), 'prayer_logs' => DB::table('prayer_logs')->where('user_id', $user->id)->get(),
            'dhikr_sessions' => DB::table('dhikr_sessions')->where('user_id', $user->id)->get(), 'zakat_calculations' => DB::table('zakat_calculations')->where('user_id', $user->id)->get(),
            'posts' => DB::table('posts')->where('user_id', $user->id)->get(), 'wallet_transactions' => DB::table('wallet_transactions')->whereIn('wallet_id', DB::table('wallets')->where('user_id', $user->id)->pluck('id'))->get(),
        ];
        AuditLogger::security('data.exported', $user->id, 'User exported their data');

        return response()->streamDownload(fn () => print (json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)), 'siraj-data-export.json', ['Content-Type' => 'application/json']);
    }

    public function delete(Request $request)
    {
        return view('app.settings.delete');
    }

    public function destroy(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password'], 'confirm' => ['accepted']]);
        $user = $request->user();
        $user->forceFill(['status' => 'pending_deletion', 'deletion_requested_at' => now()])->save();
        AuditLogger::security('account.deletion_requested', $user->id, 'Account deletion requested');
        DB::table('sessions')->where('user_id', $user->id)->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Your account is scheduled for deletion in 14 days. Contact support to cancel before then.');
    }
}
