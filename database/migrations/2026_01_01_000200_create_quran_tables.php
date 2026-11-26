<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quran_editions', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('source');
            $table->string('source_url')->nullable();
            $table->string('license')->nullable();
            $table->text('attribution')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->unsignedSmallInteger('surah_count')->default(0);
            $table->unsignedSmallInteger('ayah_count')->default(0);
            $table->timestamp('imported_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->json('verification_report')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('surahs', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary(); // = surah number (1..114)
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('meaning_en');
            $table->string('revelation', 8); // meccan | medinan
            $table->unsignedSmallInteger('ayah_count');
            $table->timestamps();
        });

        Schema::create('ayahs', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary(); // global ayah number (1..6236)
            $table->unsignedSmallInteger('surah_id');
            $table->unsignedSmallInteger('number_in_surah');
            $table->text('text_uthmani');
            $table->char('text_hash', 64); // sha256 of text_uthmani, used by the verification workflow
            $table->unsignedTinyInteger('juz');
            $table->unsignedSmallInteger('page');
            $table->unsignedTinyInteger('manzil');
            $table->unsignedSmallInteger('ruku');
            $table->unsignedSmallInteger('hizb_quarter');
            $table->boolean('sajda')->default(false);
            $table->foreign('surah_id')->references('id')->on('surahs')->cascadeOnDelete();
            $table->unique(['surah_id', 'number_in_surah']);
            $table->index('juz');
            $table->index('page');
        });

        Schema::create('juz', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedSmallInteger('first_ayah_id');
            $table->unsignedSmallInteger('last_ayah_id');
            $table->foreign('first_ayah_id')->references('id')->on('ayahs')->cascadeOnDelete();
            $table->foreign('last_ayah_id')->references('id')->on('ayahs')->cascadeOnDelete();
        });

        Schema::create('translation_sources', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('translator')->nullable();
            $table->string('language', 8);
            $table->string('source')->nullable();
            $table->string('source_url')->nullable();
            $table->string('license')->nullable();
            $table->text('attribution')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_source_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('ayah_id');
            $table->text('text');
            $table->foreign('ayah_id')->references('id')->on('ayahs')->cascadeOnDelete();
            $table->unique(['translation_source_id', 'ayah_id']);
        });

        Schema::create('tafsir_sources', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('author')->nullable();
            $table->string('language', 8);
            $table->string('source')->nullable();
            $table->string('source_url')->nullable();
            $table->string('license')->nullable();
            $table->text('attribution')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('tafsir_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tafsir_source_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('ayah_id');
            $table->text('text');
            $table->foreign('ayah_id')->references('id')->on('ayahs')->cascadeOnDelete();
            $table->unique(['tafsir_source_id', 'ayah_id']);
        });

        Schema::create('reciters', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('style', 24)->default('murattal');
            $table->string('audio_url_pattern')->nullable(); // {ayah} = global ayah number
            $table->string('source')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('audio_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reciter_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('surah_id');
            $table->unsignedSmallInteger('ayah_id')->nullable();
            $table->string('path')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->foreign('surah_id')->references('id')->on('surahs')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['reciter_id', 'surah_id', 'ayah_id']);
        });

        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('ayah_id');
            $table->string('label', 80)->nullable();
            $table->foreign('ayah_id')->references('id')->on('ayahs')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'ayah_id']);
        });

        Schema::create('ayah_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('ayah_id');
            $table->text('body');
            $table->foreign('ayah_id')->references('id')->on('ayahs')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'ayah_id']);
        });

        Schema::create('reading_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('ayah_id');
            $table->unsignedSmallInteger('surah_id');
            $table->foreign('ayah_id')->references('id')->on('ayahs')->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();
            $table->index(['user_id', 'read_at']);
        });

        Schema::create('reading_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 24)->default('ayahs'); // ayahs | khatm
            $table->unsignedInteger('daily_target')->default(10);
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('memorisation_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('ayah_id');
            $table->unsignedSmallInteger('surah_id');
            $table->string('status', 16)->default('learning'); // learning | memorised | revising
            $table->unsignedTinyInteger('strength')->default(1); // 1..5 self-assessed
            $table->timestamp('last_reviewed_at')->nullable();
            $table->foreign('ayah_id')->references('id')->on('ayahs')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'ayah_id']);
            $table->index(['user_id', 'surah_id']);
        });
    }

    public function down(): void
    {
        foreach (['memorisation_progress', 'reading_goals', 'reading_history', 'ayah_notes', 'bookmarks', 'audio_files', 'reciters', 'tafsir_entries', 'tafsir_sources', 'translations', 'translation_sources', 'juz', 'ayahs', 'surahs', 'quran_editions'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
