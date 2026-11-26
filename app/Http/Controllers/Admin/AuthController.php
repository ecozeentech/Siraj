<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\AuditLogger;
use App\Services\Admin\Totp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function create()
    {
        return view('admin.auth.login');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::guard('admin')->attempt(['email' => strtolower($data['email']), 'password' => $data['password']], false)) {
            AuditLogger::security('admin.login.failed', null, 'Failed admin sign-in', ['email' => $data['email']]);

            return back()->withErrors(['email' => 'These credentials do not match a staff account.'])->onlyInput('email');
        }
        $user = Auth::guard('admin')->user();
        if (! $user->is_staff || $user->status !== 'active') {
            Auth::guard('admin')->logout();

            return back()->withErrors(['email' => $user->is_staff ? 'This staff account is not active.' : 'This portal is for staff accounts.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        $request->session()->forget('admin.2fa');
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();
        AuditLogger::security('admin.login', $user->id, 'Admin password accepted');

        return redirect()->route($user->two_factor_confirmed_at ? 'admin.two-factor.challenge' : 'admin.two-factor.setup');
    }

    public function challenge(Request $request)
    {
        abort_unless(Auth::guard('admin')->check(), 403);

        return view('admin.auth.challenge');
    }

    public function verify(Request $request)
    {
        $user = Auth::guard('admin')->user();
        abort_unless($user && $user->two_factor_confirmed_at, 403);
        $data = $request->validate(['code' => ['required', 'string', 'max:20']]);
        $code = preg_replace('/\s+/', '', $data['code']);

        if (Totp::verify((string) $user->two_factor_secret, $code)) {
            return $this->pass($request, $user);
        }
        $codes = $user->two_factor_recovery_codes ?? [];
        foreach ($codes as $i => $stored) {
            if (hash_equals((string) $stored, $code)) {
                unset($codes[$i]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();
                AuditLogger::security('admin.2fa.recovery', $user->id, 'Admin signed in with a recovery code');

                return $this->pass($request, $user);
            }
        }
        AuditLogger::security('admin.2fa.failed', $user->id, 'Wrong admin authenticator code');

        return back()->withErrors(['code' => 'That code is not valid.']);
    }

    public function setup(Request $request)
    {
        $user = Auth::guard('admin')->user();
        abort_unless($user, 403);
        if ($user->two_factor_confirmed_at) {
            return redirect()->route('admin.dashboard');
        }
        $secret = $request->session()->get('admin.2fa_secret') ?: Totp::generateSecret();
        $request->session()->put('admin.2fa_secret', $secret);

        return view('admin.auth.setup', ['secret' => $secret, 'uri' => Totp::uri($secret, $user->email), 'codes' => $request->session()->get('admin.recovery_codes')]);
    }

    public function confirm(Request $request)
    {
        $user = Auth::guard('admin')->user();
        abort_unless($user && ! $user->two_factor_confirmed_at, 403);
        $secret = (string) $request->session()->get('admin.2fa_secret');
        abort_if($secret === '', 422, 'Start the setup again.');
        $data = $request->validate(['code' => ['required', 'string']]);
        if (! Totp::verify($secret, $data['code'])) {
            return back()->withErrors(['code' => 'That code did not match. Check the clock on your phone and try again.']);
        }
        $codes = collect(range(1, 8))->map(fn () => Str::lower(Str::random(5)).'-'.Str::lower(Str::random(5)))->all();
        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_recovery_codes' => $codes, 'two_factor_confirmed_at' => now()])->save();
        $request->session()->forget('admin.2fa_secret');
        $request->session()->put('admin.2fa', $user->id);
        $request->session()->flash('admin.recovery_codes', $codes);
        AuditLogger::security('admin.2fa.enabled', $user->id, 'Admin two-factor authentication enabled');

        return redirect()->route('admin.two-factor.setup');
    }

    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->forget(['admin.2fa', 'admin.2fa_secret']);
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function pass(Request $request, User $user)
    {
        $request->session()->put('admin.2fa', $user->id);

        return redirect()->intended(route('admin.dashboard'));
    }
}
