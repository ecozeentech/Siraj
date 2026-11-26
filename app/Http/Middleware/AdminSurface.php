<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Makes the admin guard the default for the request so gates, policies and auth() resolve the admin user. */
class AdminSurface
{
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse('admin');
        $user = Auth::guard('admin')->user();
        abort_if($user && ! $user->is_staff, 403);

        return $next($request);
    }
}
