<?php

namespace App\Http\Middleware;

use App\Services\UserProvisioner;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        $user = Auth::guard($guard)->user();
        if ($user && $guard === 'web' && ! $user->preferences()->exists()) {
            app(UserProvisioner::class)->provision($user);
            $user->unsetRelation('preferences');
        }

        if ($user && $user->status !== 'active') {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($guard === 'admin' ? 'admin.login' : 'login')->withErrors([
                'email' => $user->isSuspended() ? 'Your account has been suspended. Please contact support.' : 'This account is scheduled for deletion.',
            ]);
        }

        return $next($request);
    }
}
