<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('group', 48)->default('general')->index();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('model');
            $table->string('collection', 48)->default('default');
            $table->string('disk', 32)->default('public');
            $table->string('path');
            $table->string('name')->nullable();
            $table->string('mime', 96)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('category', 24)->default('admin')->index(); // admin | security | system
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 96)->index();
            $table->nullableMorphs('subject');
            $table->string('description', 500)->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->string('category', 32)->default('general');
            $table->text('message');
            $table->string('status', 16)->default('open')->index(); // open | pending | resolved | closed
            $table->string('priority', 12)->default('normal');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_staff')->default(false);
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('broadcast_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('category', 32)->default('system');
            $table->string('url')->nullable();
            $table->string('audience', 24)->default('all'); // all | plan | role | users
            $table->json('audience_filter')->nullable();
            $table->json('channels'); // in_app | email | push
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status', 16)->default('draft')->index(); // draft | scheduled | sending | sent | failed
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('broadcast_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 16);
            $table->string('status', 16); // delivered | failed | skipped
            $table->string('error')->nullable();
            $table->timestamps();
            $table->index(['broadcast_notification_id', 'status']);
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->string('status', 12)->default('new')->index(); // new | handled
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('region')->nullable();
            $table->string('country', 80);
            $table->string('country_code', 2)->index();
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->string('timezone', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['contact_messages', 'locations', 'broadcast_deliveries', 'broadcast_notifications', 'support_ticket_replies', 'support_tickets', 'audit_logs', 'media', 'app_settings'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
