<?php

namespace App\Http\Middleware;

use App\Models\Family\ChildProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * While a child profile is active, the session may only reach Kids routes. Everything else
 * (community, messaging, AI, wallet, settings…) is blocked, and daily screen-time limits are enforced.
 */
class KidsMode
{
    public function handle(Request $request, Closure $next): Response
    {
        $childId = $request->session()->get('kids_child_id');
        if (! $childId) {
            return $next($request);
        }

        $child = ChildProfile::query()->with('family', 'controls')->find($childId);
        if (! $child || $child->family->owner_id !== $request->user()?->id) {
            $request->session()->forget('kids_child_id');

            return $next($request);
        }

        $allowed = $request->routeIs('app.kids.*') || $request->routeIs('logout') || $request->is('app/sync');
        if (! $allowed) {
            return redirect()->route('app.kids.home');
        }

        $limit = (int) $child->controls->daily_limit_minutes;
        $used = (int) \DB::table('child_screen_time')->where('child_profile_id', $child->id)->where('day', today()->toDateString())->value('seconds');
        if ($limit > 0 && $used >= $limit * 60 && ! $request->routeIs('app.kids.limit', 'app.kids.exit', 'app.kids.heartbeat')) {
            return redirect()->route('app.kids.limit');
        }

        $request->attributes->set('kids_child', $child);

        return $next($request);
    }
}
