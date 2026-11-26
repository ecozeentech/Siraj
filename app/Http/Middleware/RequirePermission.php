<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Backend enforcement of granular admin permissions. Usage: ->middleware('permission:users.manage') */
class RequirePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = Auth::guard('admin')->user();
        abort_unless($user && $user->is_staff, 403);
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }
        abort(403, 'You do not have permission to perform this action.');
    }
}
