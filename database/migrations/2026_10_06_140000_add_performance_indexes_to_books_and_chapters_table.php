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
        Schema::table('books', function (Blueprint $table) {
            $table->index('status', 'books_status_index');
        });

        Schema::table('chapters', function (Blueprint $table) {
            $table->index('status', 'chapters_status_index');
            $table->unique(['book_id', 'chapter_number'], 'chapters_book_id_chapter_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropUnique('chapters_book_id_chapter_number_unique');
            $table->dropIndex('chapters_status_index');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex('books_status_index');
        });
    }
};
