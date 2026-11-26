<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Staff must confirm an authenticator app before any admin page. A confirmed account must pass a code once per session. */
class EnsureAdminTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();
        if (! $user) {
            return $next($request);
        }
        if (! $user->two_factor_confirmed_at) {
            return redirect()->route('admin.two-factor.setup');
        }
        if ((int) $request->session()->get('admin.2fa') !== (int) $user->id) {
            return redirect()->route('admin.two-factor.challenge');
        }

        return $next($request);
    }
}
