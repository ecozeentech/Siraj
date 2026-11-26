<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt(['email' => strtolower($credentials['email']), 'password' => $credentials['password']], $request->boolean('remember'))) {
            AuditLogger::security('login.failed', null, 'Failed sign-in attempt', ['email' => $credentials['email']]);

            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $user = Auth::user();
        if ($user->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();

            return back()->withErrors(['email' => $user->isSuspended() ? 'Your account has been suspended. Please contact support.' : 'This account is scheduled for deletion.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();

        return redirect()->intended(route('app.home'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->forget('kids_child_id');
        if (! Auth::guard('admin')->check()) {
            $request->session()->invalidate();
        }
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
