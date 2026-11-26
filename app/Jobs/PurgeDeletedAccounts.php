<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Admin\AuditLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Completes account deletion after the 14-day grace period. Ledger rows stay attached to the (anonymised) user. */
class PurgeDeletedAccounts implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        User::query()->where('status', 'pending_deletion')->where('deletion_requested_at', '<=', now()->subDays(14))
            ->each(function (User $user) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
                $user->forceFill([
                    'name' => 'Deleted member',
                    'email' => 'deleted-'.$user->id.'@deleted.sirajmuslim.invalid',
                    'phone' => null,
                    'avatar_path' => null,
                    'password' => Hash::make(Str::random(40)),
                    'two_factor_secret' => null,
                    'two_factor_recovery_codes' => null,
                    'two_factor_confirmed_at' => null,
                    'remember_token' => null,
                    'status' => 'deleted',
                    'is_staff' => false,
                ])->save();
                $user->roles()->detach();
                $user->delete();
                AuditLogger::log('account.purged', $user, 'Account anonymised after the 14-day deletion grace period.', [], 'system', null);
            });
    }
}
