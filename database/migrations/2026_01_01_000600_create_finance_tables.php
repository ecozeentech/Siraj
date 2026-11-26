<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('price_kobo')->default(0);
            $table->string('currency', 3)->default('NGN');
            $table->string('interval', 12)->default('month'); // free | month | year | lifetime
            $table->json('features')->nullable();
            $table->json('entitlements')->nullable(); // ai_daily_limit, family_children, offline_downloads, ...
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 64)->unique();
            $table->string('gateway', 24)->default('paystack');
            $table->string('mode', 8)->default('sandbox');
            $table->string('purpose', 24); // subscription | wallet_topup | event_ticket
            $table->nullableMorphs('payable');
            $table->unsignedBigInteger('amount_kobo');
            $table->string('currency', 3)->default('NGN');
            $table->string('status', 20)->default('pending')->index(); // pending | succeeded | failed | abandoned | refunded | partially_refunded
            $table->string('gateway_reference')->nullable();
            $table->text('authorization_url')->nullable();
            $table->string('access_code')->nullable();
            $table->string('channel', 24)->nullable();
            $table->json('gateway_response')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->string('status', 16)->default('active')->index(); // active | cancelled | expired | pending
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->foreignId('payment_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 16)->default('gateway'); // gateway | wallet | admin
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('gateway', 24)->default('paystack');
            $table->text('authorization_code'); // encrypted by model cast
            $table->string('brand', 24)->nullable();
            $table->string('last4', 4)->nullable();
            $table->string('exp_month', 2)->nullable();
            $table->string('exp_year', 4)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('currency', 3)->default('NGN');
            $table->bigInteger('balance_kobo')->default(0); // cached; the ledger is the source of truth
            $table->timestamps();
        });

        Schema::create('wallet_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 48)->unique(); // user:{id}:wallet | system:subscription_revenue | ...
            $table->string('name');
            $table->string('type', 12); // asset | liability | revenue | expense
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();
            $table->string('currency', 3)->default('NGN');
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('group_id')->index();
            $table->foreignId('wallet_account_id')->constrained()->restrictOnDelete();
            $table->string('direction', 6); // debit | credit
            $table->unsignedBigInteger('amount_kobo');
            $table->string('currency', 3)->default('NGN');
            $table->string('description')->nullable();
            $table->string('reference', 80)->nullable()->index();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->uuid('ledger_group_id')->index();
            $table->string('type', 8); // credit | debit
            $table->unsignedBigInteger('amount_kobo');
            $table->bigInteger('balance_after_kobo');
            $table->string('description');
            $table->string('reference', 80)->unique();
            $table->nullableMorphs('source');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_transaction_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount_kobo');
            $table->string('method', 12)->default('wallet'); // wallet | gateway
            $table->string('reason', 500);
            $table->string('status', 12)->default('requested')->index(); // requested | processed | rejected | failed
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('gateway_refund_id')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['refunds', 'wallet_transactions', 'ledger_entries', 'wallet_accounts', 'wallets', 'payment_methods', 'subscriptions', 'payment_transactions', 'subscription_plans'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
