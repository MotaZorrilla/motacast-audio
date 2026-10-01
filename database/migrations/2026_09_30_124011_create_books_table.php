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
            $table->string('title');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->string('original_filename');
            $table->string('pdf_path');
            $table->string('voice')->default('es-VE-SebastianNeural');
            $table->string('speed_rate')->default('+0%');
            $table->string('pitch')->default('+0Hz');
            $table->string('status')->default('pending'); // pending, extracting, synthesizing, ready, failed
            $table->unsignedInteger('total_chapters')->default(0);
            $table->unsignedInteger('processed_chapters')->default(0);
            $table->unsignedInteger('total_duration')->default(0); // in seconds
            $table->unsignedInteger('total_words')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
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
