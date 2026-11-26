<?php

namespace App\Http\Middleware;

use App\Services\Settings;
use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerifiedWhenRequired
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $required = Settings::has('auth.require_verified_email')
            ? filter_var(Settings::get('auth.require_verified_email'), FILTER_VALIDATE_BOOLEAN)
            : (bool) config('siraj.require_verified_email');
        if ($required && $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Your email address is not verified.'], 403)
                : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
