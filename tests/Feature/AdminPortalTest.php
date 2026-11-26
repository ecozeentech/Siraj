<?php

namespace Tests\Feature;

use App\Models\System\Location;
use App\Models\User;
use App\Services\Admin\Totp;
use App\Services\Finance\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPortalTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $email): User
    {
        $user = User::query()->where('email', $email)->firstOrFail();
        $user->forceFill([
            'two_factor_secret' => Totp::generateSecret(),
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => ['rescue-code'],
        ])->save();

        return $user;
    }

    private function asStaff(User $user): static
    {
        return $this->actingAs($user, 'admin')->withSession(['admin.2fa' => $user->id]);
    }

    public function test_staff_portal_pages_and_permissions(): void
    {
        $this->seed();
        $this->get('/admin/login')->assertOk();
        $this->actingAs(User::query()->where('email', 'ahmed@example.com')->firstOrFail())->get('/admin')->assertForbidden();

        $content = $this->staff('content-manager@sirajmuslim.test');
        $this->asStaff($content)->get('/admin')->assertOk();
        $this->asStaff($content)->get('/admin/payments')->assertForbidden();
        $this->asStaff($content)->get('/admin/worship')->assertOk();

        $admin = $this->staff('super-admin@sirajmuslim.test');
        foreach (['/admin', '/admin/users', '/admin/roles', '/admin/support', '/admin/community', '/admin/notifications', '/admin/ai', '/admin/payments', '/admin/subscriptions', '/admin/reports', '/admin/audit', '/admin/settings', '/admin/quran', '/admin/worship', '/admin/academy', '/admin/kids', '/admin/events', '/admin/directories'] as $path) {
            $this->asStaff($admin)->get($path)->assertOk();
        }

        $this->asStaff($admin)->post('/admin/worship/locations', [
            'name' => 'Ilorin', 'region' => 'Kwara', 'country' => 'Nigeria', 'country_code' => 'NG',
            'latitude' => 8.5, 'longitude' => 4.55, 'timezone' => 'Africa/Lagos',
        ])->assertRedirect();
        $this->assertDatabaseHas('locations', ['name' => 'Ilorin']);
        $this->asStaff($admin)->get('/admin/worship/locations/'.Location::query()->where('name', 'Ilorin')->value('id').'/edit')->assertOk();
    }

    public function test_wallet_adjustment_is_a_balanced_ledger_entry(): void
    {
        $this->seed();
        $admin = $this->staff('super-admin@sirajmuslim.test');
        $member = User::query()->where('email', 'ahmed@example.com')->firstOrFail();
        $before = (int) $member->wallet->balance_kobo;

        $this->asStaff($admin)->post('/admin/payments/adjust', [
            'email' => $member->email, 'direction' => 'credit', 'amount' => 1500, 'reason' => 'Goodwill credit for a failed event',
        ])->assertRedirect()->assertSessionHas('status');
        $this->assertSame($before + 150000, (int) $member->wallet->fresh()->balance_kobo);

        $this->asStaff($admin)->post('/admin/payments/adjust', [
            'email' => $member->email, 'direction' => 'debit', 'amount' => 999999, 'reason' => 'This debit is larger than the balance',
        ])->assertRedirect()->assertSessionHas('error');
        $this->assertSame($before + 150000, (int) $member->wallet->fresh()->balance_kobo);
        $this->assertTrue(app(LedgerService::class)->reconcile($member->wallet->fresh()));
    }

    public function test_admin_sign_in_requires_a_second_factor(): void
    {
        $this->seed();
        $this->post('/admin/login', ['email' => 'ahmed@example.com', 'password' => 'password'])->assertSessionHasErrors('email');

        $this->post('/admin/login', ['email' => 'super-admin@sirajmuslim.test', 'password' => 'password'])->assertRedirect(route('admin.two-factor.setup'));
        $this->get('/admin')->assertRedirect(route('admin.two-factor.setup'));
        $this->get('/admin/two-factor/setup')->assertOk();
        $this->post('/admin/two-factor/confirm', ['code' => '000000'])->assertSessionHasErrors('code');
        $secret = session('admin.2fa_secret');
        $this->post('/admin/two-factor/confirm', ['code' => Totp::code($secret)])->assertRedirect(route('admin.two-factor.setup'));
        $this->get('/admin')->assertOk();
    }
}
