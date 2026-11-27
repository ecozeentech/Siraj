<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('code', 12)->unique();
            $table->unsignedInteger('balance')->default(0);
            $table->timestamps();
        });

        Schema::create('point_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('points');
            $table->string('reason', 40);
            $table->nullableMorphs('subject');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_providers', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 24)->unique();
            $table->string('name');
            $table->string('driver', 24);
            $table->boolean('enabled')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->text('instructions')->nullable();
            $table->json('public_details')->nullable();
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['paystack', 'Paystack', 'paystack', true, 1, null, null],
            ['stripe', 'Stripe', 'stripe', false, 2, null, null],
            ['flutterwave', 'Flutterwave', 'flutterwave', false, 3, null, null],
            ['opay', 'OPay', 'opay', false, 4, 'Pay with OPay, then wait for confirmation.', null],
            ['crypto', 'Crypto', 'manual', false, 5, 'Send the exact amount to the wallet address and keep the transaction hash.', '{"address":""}'],
            ['bank', 'Bank transfer', 'manual', false, 6, 'Transfer the exact amount and use your payment reference as the narration.', '{"bank":"","account_name":"","account_number":""}'],
        ];
        foreach ($rows as $row) {
            DB::table('payment_providers')->insert([
                'slug' => $row[0],
                'name' => $row[1],
                'driver' => $row[2],
                'enabled' => $row[3],
                'sort' => $row[4],
                'instructions' => $row[5],
                'public_details' => $row[6],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('point_entries');
        Schema::dropIfExists('referral_accounts');
        Schema::dropIfExists('payment_providers');
    }
};
