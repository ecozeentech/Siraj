<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('icon', 48)->default('book-open');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->text('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('summary', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('level', 16)->default('beginner');
            $table->string('cover_theme', 24)->default('mosque_green'); // preset illustration (see x-ui.cover)
            $table->string('cover_path')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type', 8)->default('text'); // text | video | audio
            $table->longText('body')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_path')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(5);
            $table->boolean('is_free_preview')->default(false);
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('lesson_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type', 16)->default('link'); // link | pdf | audio
            $table->string('url')->nullable();
            $table->string('path')->nullable();
            $table->timestamps();
        });

        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->unsignedTinyInteger('pass_mark')->default(70);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->string('type', 16)->default('single'); // single | multiple | true_false
            $table->text('explanation')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete();
            $table->string('body');
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
        });

        Schema::create('course_enrolments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->string('certificate_code', 24)->nullable()->unique();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
        });

        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_lesson_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('position_seconds')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'course_lesson_id']);
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score'); // percentage
            $table->unsignedSmallInteger('correct_count');
            $table->unsignedSmallInteger('question_count');
            $table->boolean('passed');
            $table->json('responses');
            $table->timestamp('submitted_at')->useCurrent();
            $table->index(['user_id', 'quiz_id']);
        });

        // ---- Kids ----
        Schema::create('kids_courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 24)->default('quran'); // quran | arabic | manners | faith
            $table->string('summary', 300)->nullable();
            $table->unsignedTinyInteger('age_min')->default(4);
            $table->unsignedTinyInteger('age_max')->default(12);
            $table->string('icon', 48)->default('book-open');
            $table->string('color', 16)->default('green');
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('kids_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kids_course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->string('arabic_text')->nullable();
            $table->string('transliteration')->nullable();
            $table->string('audio_url')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('kids_stories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('body');
            $table->string('moral')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedTinyInteger('age_min')->default(4);
            $table->unsignedTinyInteger('age_max')->default(12);
            $table->string('cover_theme', 24)->default('mosque_green');
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('kids_games', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type', 16)->default('match'); // match | quiz
            $table->string('summary', 300)->nullable();
            $table->json('config');
            $table->unsignedTinyInteger('age_min')->default(4);
            $table->unsignedTinyInteger('age_max')->default(12);
            $table->string('icon', 48)->default('gamepad-2');
            $table->string('color', 16)->default('gold');
            $table->boolean('is_published')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('kids_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_profile_id')->constrained()->cascadeOnDelete();
            $table->string('content_type', 16); // lesson | story | game
            $table->unsignedBigInteger('content_id');
            $table->unsignedTinyInteger('stars')->default(0);
            $table->unsignedSmallInteger('score')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['child_profile_id', 'content_type', 'content_id']);
        });

        // ---- AI ----
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('driver', 24)->default('openai_compatible');
            $table->string('base_url')->nullable();
            $table->text('api_key')->nullable(); // stored encrypted via model cast; never exposed to the frontend
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_provider_id')->constrained()->cascadeOnDelete();
            $table->string('model_id');
            $table->string('label');
            $table->decimal('input_cost_per_million', 10, 4)->default(0);
            $table->decimal('output_cost_per_million', 10, 4)->default(0);
            $table->unsignedInteger('max_output_tokens')->default(800);
            $table->decimal('temperature', 3, 2)->default(0.2);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ai_sources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type', 16)->default('article'); // quran | hadith | book | article | fatwa
            $table->string('reference')->nullable();
            $table->string('url')->nullable();
            $table->text('content');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->default('New conversation');
            $table->foreignId('ai_model_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('role', 12); // user | assistant
            $table->longText('content');
            $table->json('citations')->nullable();
            $table->string('status', 16)->default('ok'); // ok | unconfigured | error | limited
            $table->unsignedInteger('tokens_in')->default(0);
            $table->unsignedInteger('tokens_out')->default(0);
            $table->timestamps();
        });

        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_message_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_model_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('tokens_in')->default(0);
            $table->unsignedInteger('tokens_out')->default(0);
            $table->decimal('cost_usd', 12, 6)->default(0);
            $table->unsignedInteger('latency_ms')->default(0);
            $table->string('status', 16)->default('ok');
            $table->string('error')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('ai_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reason', 32);
            $table->text('details')->nullable();
            $table->string('status', 16)->default('open'); // open | reviewed | dismissed
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['ai_message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        foreach (['ai_reports', 'ai_usage_logs', 'ai_messages', 'ai_conversations', 'ai_sources', 'ai_models', 'ai_providers', 'kids_progress', 'kids_games', 'kids_stories', 'kids_lessons', 'kids_courses', 'quiz_attempts', 'lesson_progress', 'course_enrolments', 'quiz_answers', 'quiz_questions', 'quizzes', 'lesson_resources', 'course_lessons', 'courses', 'instructors', 'course_categories'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
