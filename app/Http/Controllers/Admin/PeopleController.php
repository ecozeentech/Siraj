<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community\UserRestriction;
use App\Models\Permission;
use App\Models\Role;
use App\Models\System\SupportTicket;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\Admin\AuditLogger;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PeopleController extends Controller
{
    public function users(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $users = User::query()->with('roles')->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->when($request->query('status'), fn ($w, $s) => $w->where('status', $s))->latest('id')->paginate(20)->withQueryString();

        return view('admin.people.users', ['users' => $users, 'q' => $q]);
    }

    public function show(User $user)
    {
        $user->load(['roles', 'profile', 'wallet', 'activeSubscription.plan']);

        return view('admin.people.show', [
            'member' => $user, 'roles' => Role::query()->orderBy('name')->get(),
            'restrictions' => UserRestriction::query()->where('user_id', $user->id)->latest()->limit(10)->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $actor = Auth::guard('admin')->user();
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'roles' => ['array'], 'roles.*' => ['exists:roles,id']]);
        $roles = Role::query()->whereIn('id', $data['roles'] ?? [])->get();
        if ($roles->contains('slug', 'super-admin') && ! $actor->isSuperAdmin()) {
            return back()->with('error', 'Only a super administrator can grant that role.');
        }
        if ($user->isSuperAdmin() && ! $roles->contains('slug', 'super-admin') && $this->superAdminCount() <= 1) {
            return back()->with('error', 'There must be at least one super administrator.');
        }
        $user->forceFill(['name' => $data['name']])->save();
        $user->roles()->sync($roles->pluck('id'));
        $user->flushPermissionCache();
        AuditLogger::log('users.updated', $user, 'Updated member profile and roles', ['roles' => $roles->pluck('slug')->all()]);

        return back()->with('status', 'Member updated.');
    }

    public function suspend(Request $request, User $user)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);
        abort_if($user->id === Auth::guard('admin')->id(), 422, 'You cannot suspend your own account.');
        $user->forceFill(['status' => 'suspended', 'suspended_at' => now(), 'suspension_reason' => $data['reason']])->save();
        DB::table('sessions')->where('user_id', $user->id)->delete();
        AuditLogger::log('users.suspended', $user, $data['reason']);

        return back()->with('status', $user->name.' is suspended and signed out.');
    }

    public function activate(User $user)
    {
        $user->forceFill(['status' => 'active', 'suspended_at' => null, 'suspension_reason' => null, 'deletion_requested_at' => null])->save();
        AuditLogger::log('users.activated', $user, 'Account reactivated');

        return back()->with('status', $user->name.' is active again.');
    }

    public function staff(Request $request, User $user)
    {
        abort_unless(Auth::guard('admin')->user()->isSuperAdmin(), 403);
        abort_if($user->id === Auth::guard('admin')->id() && $user->is_staff, 422, 'You cannot remove your own staff access.');
        $user->forceFill(['is_staff' => ! $user->is_staff])->save();
        if (! $user->is_staff) {
            $user->roles()->detach();
        }
        AuditLogger::log('users.staff', $user, $user->is_staff ? 'Granted staff access' : 'Removed staff access');

        return back()->with('status', $user->is_staff ? 'Staff access granted. Assign a role before they can do anything.' : 'Staff access removed.');
    }

    public function deletion(User $user)
    {
        if ($user->status === 'pending_deletion') {
            $user->forceFill(['status' => 'active', 'deletion_requested_at' => null])->save();
            $message = 'Deletion cancelled.';
        } else {
            abort_if($user->id === Auth::guard('admin')->id(), 422);
            $user->forceFill(['status' => 'pending_deletion', 'deletion_requested_at' => now()])->save();
            $message = 'Deletion scheduled. The account is anonymised after 14 days.';
        }
        AuditLogger::log('users.deletion', $user, $message);

        return back()->with('status', $message);
    }

    public function roles()
    {
        return view('admin.people.roles', ['roles' => Role::query()->withCount('users')->with('permissions')->orderBy('name')->get(), 'catalogue' => Permissions::ALL]);
    }

    public function storeRole(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80'], 'description' => ['nullable', 'string', 'max:255']]);
        $slug = Str::slug($data['name']);
        abort_if(Role::query()->where('slug', $slug)->exists() || $slug === '', 422);
        $role = Role::query()->create(['name' => $data['name'], 'slug' => $slug, 'description' => $data['description'] ?? null, 'is_system' => false]);
        AuditLogger::log('roles.created', $role, 'Created role '.$role->name);

        return redirect()->route('admin.roles.index')->with('status', 'Role created. Set its permissions below.');
    }

    public function updateRole(Request $request, Role $role)
    {
        abort_if($role->slug === 'super-admin', 403, 'The super administrator role always has every permission.');
        $slugs = Permissions::slugs();
        $data = $request->validate(['permissions' => ['array'], 'permissions.*' => [Rule::in($slugs)], 'name' => ['required', 'string', 'max:80'], 'description' => ['nullable', 'string', 'max:255']]);
        if (! $role->is_system) {
            $role->update(['name' => $data['name'], 'description' => $data['description'] ?? null]);
        }
        $role->permissions()->sync(Permission::query()->whereIn('slug', $data['permissions'] ?? [])->pluck('id'));
        AuditLogger::log('roles.updated', $role, 'Updated permissions for '.$role->name);

        return back()->with('status', 'Permissions saved. Staff will see the change on their next request.');
    }

    public function tickets(Request $request)
    {
        return view('admin.people.tickets', ['tickets' => SupportTicket::query()->with('user')->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))->latest()->paginate(20)->withQueryString()]);
    }

    public function ticket(SupportTicket $ticket)
    {
        return view('admin.people.ticket', ['ticket' => $ticket->load(['user', 'replies.user'])]);
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:5000'], 'status' => ['required', Rule::in(['open', 'pending', 'resolved', 'closed'])]]);
        $ticket->replies()->create(['user_id' => Auth::guard('admin')->id(), 'is_staff' => true, 'body' => trim(strip_tags($data['body']))]);
        $ticket->update(['status' => $data['status'], 'assigned_to' => Auth::guard('admin')->id()]);
        $ticket->user->notify(new GeneralNotification('Reply to your support request', 'We replied to “'.$ticket->subject.'”.', '/app/support/'.$ticket->id, 'system', 'life-buoy'));
        AuditLogger::log('support.replied', $ticket, 'Replied to ticket '.$ticket->id);

        return back()->with('status', 'Reply sent.');
    }

    private function superAdminCount(): int
    {
        return User::query()->where('is_staff', true)->whereHas('roles', fn ($q) => $q->where('slug', 'super-admin'))->count();
    }
}
