<?php

namespace Tests\Feature;

use App\Models\Ai\AiConversation;
use App\Models\Quran\Bookmark;
use App\Models\User;
use App\Services\Ai\AiService;
use App\Services\Finance\GatewayNotConfiguredException;
use App\Services\Finance\LedgerService;
use App\Services\Finance\PaymentService;
use App\Services\Settings;
use App\Services\Sync\SyncProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class CoreWorkflowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_offline_sync_applies_a_bookmark_once(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'ahmed@example.com')->firstOrFail();
        $sync = app(SyncProcessor::class);
        $action = ['id' => 'client-bookmark-1', 'type' => 'bookmark.toggle', 'payload' => ['ayah_id' => 1, 'on' => true]];

        $first = $sync->process($user, [$action, ['id' => 'nope', 'type' => 'not-real', 'payload' => []]]);
        $second = $sync->process($user, [$action]);

        $this->assertSame(['client-bookmark-1', 'nope'], $first['processed']);
        $this->assertSame(['nope' => 'unknown_type'], $first['rejected']);
        $this->assertSame(['client-bookmark-1'], $second['processed']);
        $this->assertSame(1, Bookmark::query()->where('user_id', $user->id)->where('ayah_id', 1)->count());
    }

    public function test_paystack_topup_credits_the_wallet_once(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'ahmed@example.com')->firstOrFail();
        $this->expectException(GatewayNotConfiguredException::class);
        app(PaymentService::class)->initiate($user, 'wallet_topup', 500000);
    }

    public function test_configured_paystack_payment_is_idempotent(): void
    {
        $this->seed();
        Settings::set('paystack.secret_key', 'sk_test_example', 'payments', true);
        Settings::set('paystack.public_key', 'pk_test_example', 'payments');
        Http::fake([
            'https://api.paystack.co/transaction/initialize' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.paystack.com/test', 'access_code' => 'ac']]),
            'https://api.paystack.co/transaction/verify/*' => Http::response(['status' => true, 'data' => ['status' => 'success', 'amount' => 500000, 'currency' => 'NGN', 'channel' => 'card', 'id' => 99]]),
        ]);
        $user = User::query()->where('email', 'ahmed@example.com')->firstOrFail();
        $before = (int) $user->wallet->balance_kobo;
        $tx = app(PaymentService::class)->initiate($user, 'wallet_topup', 500000);
        $paid = app(PaymentService::class)->verifyAndFulfil($tx);
        app(PaymentService::class)->verifyAndFulfil($paid->fresh());

        $this->assertSame('succeeded', $paid->status);
        $this->assertSame($before + 500000, (int) $user->wallet->fresh()->balance_kobo);
        $this->assertTrue(app(LedgerService::class)->reconcile($user->wallet->fresh()));
        Http::assertSentCount(2);
    }

    public function test_ai_guard_drops_quran_references_that_were_not_retrieved(): void
    {
        $method = new ReflectionMethod(AiService::class, 'guard');
        [$text, $flags] = $method->invoke(app(AiService::class), 'Read Quran 2:255 and also Quran 9:5.', [
            ['id' => 'S1', 'type' => 'quran', 'ref' => '2:255', 'title' => 'Quran 2:255', 'text' => 'Ayat al-Kursi'],
        ]);

        $this->assertStringContainsString('2:255', $text);
        $this->assertStringNotContainsString('9:5', $text);
        $this->assertContains('quran_ref_removed', $flags);
    }

    public function test_opening_the_quran_provisions_missing_preferences(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'ahmed@example.com')->firstOrFail();
        $user->preferences()->delete();

        $this->actingAs($user)->get('/app/quran/surah/1')->assertOk();
        $this->assertNotNull($user->fresh()->preferences);
    }

    public function test_unconfigured_ai_does_not_invent_an_answer(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'ahmed@example.com')->firstOrFail();
        $conversation = AiConversation::query()->create(['user_id' => $user->id, 'title' => 'New conversation']);
        $message = app(AiService::class)->ask($user, $conversation, 'What does 2:255 say?');

        $this->assertSame('unconfigured', $message->status);
        $this->assertStringContainsString("isn't connected", $message->content);
    }
}
