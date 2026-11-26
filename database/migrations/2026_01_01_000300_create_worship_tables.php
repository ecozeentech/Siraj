<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('location_name')->default('Lagos, Nigeria');
            $table->decimal('latitude', 9, 6)->default(6.524400);
            $table->decimal('longitude', 9, 6)->default(3.379200);
            $table->string('timezone', 64)->default('Africa/Lagos');
            $table->string('method', 24)->default('mwl');
            $table->string('asr_method', 12)->default('standard'); // standard (Shafi'i/Maliki/Hanbali) | hanafi
            $table->string('high_latitude_rule', 16)->default('angle_based');
            $table->json('adjustments')->nullable(); // minute offsets per prayer
            $table->unsignedSmallInteger('hijri_offset')->default(0);
            $table->boolean('reminders_enabled')->default(false);
            $table->unsignedSmallInteger('remind_minutes_before')->default(10);
            $table->timestamps();
        });

        Schema::create('qibla_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('use_prayer_location')->default(true);
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->smallInteger('calibration_offset')->default(0);
            $table->timestamps();
        });

        Schema::create('prayer_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('day');
            $table->string('prayer', 12); // fajr | dhuhr | asr | maghrib | isha
            $table->string('status', 12)->default('on_time'); // on_time | late | missed | qada
            $table->boolean('in_congregation')->default(false);
            $table->timestamp('prayed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'day', 'prayer']);
        });

        Schema::create('adhkar_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->string('group', 24)->default('daily'); // morning | evening | after_prayer | sleep | daily | other
            $table->string('source')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('adhkar_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adhkar_category_id')->constrained()->cascadeOnDelete();
            $table->text('text_ar');
            $table->text('transliteration')->nullable();
            $table->text('translation_en')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedSmallInteger('repeat_count')->default(1);
            $table->string('audio_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('adhkar_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('adhkar_entry_id')->constrained()->cascadeOnDelete();
            $table->date('day');
            $table->unsignedSmallInteger('count')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'adhkar_entry_id', 'day']);
        });

        Schema::create('adhkar_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('adhkar_entry_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'adhkar_entry_id']);
        });

        Schema::create('dhikr_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phrase', 120)->default('SubhanAllah');
            $table->unsignedInteger('count')->default(0);
            $table->unsignedInteger('target')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('tajweed_resources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('rule_group', 48)->nullable();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('example_ar')->nullable();
            $table->string('audio_url')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('type', 16)->default('umrah'); // hajj | umrah | travel | duas
            $table->text('summary')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('guide_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->text('arabic_text')->nullable();
            $table->text('transliteration')->nullable();
            $table->text('translation_en')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('guide_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_id')->constrained()->cascadeOnDelete();
            $table->string('category', 48)->default('general');
            $table->string('label');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('saved_guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guide_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'guide_id']);
        });

        Schema::create('checklist_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guide_checklist_item_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at')->useCurrent();
            $table->unique(['user_id', 'guide_checklist_item_id']);
        });

        Schema::create('zakat_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('currency', 3)->default('NGN');
            $table->string('nisab_basis', 8)->default('silver'); // gold | silver
            $table->decimal('gold_price_per_gram', 14, 2)->default(0);
            $table->decimal('silver_price_per_gram', 14, 2)->default(0);
            $table->boolean('hawl_completed')->default(true);
            $table->json('inputs');
            $table->decimal('total_assets', 16, 2)->default(0);
            $table->decimal('total_liabilities', 16, 2)->default(0);
            $table->decimal('net_wealth', 16, 2)->default(0);
            $table->decimal('nisab_value', 16, 2)->default(0);
            $table->decimal('zakat_due', 16, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['zakat_calculations', 'checklist_progress', 'saved_guides', 'guide_checklist_items', 'guide_steps', 'guides', 'tajweed_resources', 'dhikr_sessions', 'adhkar_favorites', 'adhkar_progress', 'adhkar_entries', 'adhkar_categories', 'prayer_logs', 'qibla_preferences', 'prayer_preferences'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
