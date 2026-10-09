<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_fingerprint', 120)->nullable()->index();
            $table->string('country_code', 5)->nullable()->index();
            $table->string('title');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->text('summary')->nullable();
            $table->string('summary_audio_path')->nullable();
            $table->string('original_filename');
            $table->string('pdf_path');
            $table->string('voice')->default('es-VE-SebastianNeural');
            $table->string('speed_rate')->default('+0%');
            $table->string('pitch')->default('+0Hz');
            $table->boolean('keep_original_media')->default(false);
            $table->string('status')->default('pending')->index(); // pending, extracting, synthesizing, ready, failed
            $table->unsignedInteger('total_chapters')->default(0);
            $table->unsignedInteger('processed_chapters')->default(0);
            $table->unsignedInteger('total_duration')->default(0); // in seconds
            $table->unsignedInteger('total_words')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['guest_fingerprint', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
