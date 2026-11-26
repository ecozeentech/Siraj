<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone', 32)->nullable()->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar_path')->nullable();
            $table->string('status', 24)->default('active')->index(); // active | suspended | pending_deletion
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->timestamp('deletion_requested_at')->nullable();
            $table->boolean('is_staff')->default(false)->index();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamp('onboarded_at')->nullable();
            $table->string('locale', 8)->default('en');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Laravel's session store doubles as the "user_sessions" table (user_id + ip + agent + last_activity).
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('username', 40)->nullable()->unique();
            $table->string('bio', 280)->nullable();
            $table->string('gender', 12)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('country', 80)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('profile_visibility', 16)->default('public'); // public | followers | private
            $table->string('message_privacy', 16)->default('followers'); // everyone | followers | nobody
            $table->boolean('show_location')->default(true);
            $table->boolean('share_prayer_activity')->default(false);
            $table->timestamps();
        });

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('language', 8)->default('en');
            $table->string('theme', 12)->default('light'); // light | dark | system
            $table->string('timezone', 64)->default('Africa/Lagos');
            // Quran reading preferences
            $table->unsignedSmallInteger('quran_font_size')->default(32);
            $table->string('quran_reading_mode', 16)->default('continuous'); // continuous | page | ayah
            $table->foreignId('translation_source_id')->nullable();
            $table->foreignId('tafsir_source_id')->nullable();
            $table->foreignId('reciter_id')->nullable();
            $table->boolean('show_translation')->default(true);
            $table->boolean('show_tafsir')->default(true);
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('group', 48)->index();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'user_id']);
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 16)->default('member'); // parent | guardian | member
            $table->timestamps();
            $table->unique(['family_id', 'user_id']);
        });

        Schema::create('child_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->unsignedSmallInteger('birth_year');
            $table->string('avatar', 16)->default('boy'); // boy | girl
            $table->string('pin_hash')->nullable();
            $table->timestamps();
        });

        Schema::create('parental_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('daily_limit_minutes')->default(60);
            $table->json('allowed_sections')->nullable(); // courses | stories | games
            $table->boolean('require_parent_approval')->default(false);
            $table->timestamps();
        });

        Schema::create('child_screen_time', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->cascadeOnDelete();
            $table->date('day');
            $table->unsignedInteger('seconds')->default(0);
            $table->unique(['child_profile_id', 'day']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 32); // prayer | quran | academy | community | events | system | marketing
            $table->boolean('in_app')->default(true);
            $table->boolean('email')->default(false);
            $table->boolean('push')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'category']);
        });

        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('endpoint_hash', 64)->unique();
            $table->text('endpoint');
            $table->string('public_key');
            $table->string('auth_token');
            $table->string('content_encoding', 16)->default('aesgcm');
            $table->timestamps();
        });

        Schema::create('sync_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('client_id', 64);
            $table->string('type', 48);
            $table->timestamps();
            $table->unique(['user_id', 'client_id']);
        });
    }

    public function down(): void
    {
        foreach (['sync_receipts', 'push_subscriptions', 'notification_preferences', 'notifications', 'child_screen_time', 'parental_controls', 'child_profiles', 'family_members', 'families', 'permission_role', 'role_user', 'permissions', 'roles', 'user_preferences', 'user_profiles', 'sessions', 'password_reset_tokens', 'users'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
