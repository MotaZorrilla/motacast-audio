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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->unsignedInteger('chapter_number');
            $table->string('title');
            $table->longText('content_text');
            $table->string('audio_path')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedInteger('word_count')->default(0);
            $table->string('status')->default('pending')->index(); // pending, synthesizing, ready, failed
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['book_id', 'chapter_number'], 'chapters_book_id_chapter_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
