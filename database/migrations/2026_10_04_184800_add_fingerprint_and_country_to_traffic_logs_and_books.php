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
        Schema::table('traffic_logs', function (Blueprint $table) {
            $table->string('country_code', 5)->nullable()->default('LOC')->index()->after('user_id');
            $table->string('country_name', 50)->nullable()->after('country_code');
            $table->string('country_flag', 10)->nullable()->after('country_name');
            $table->string('guest_fingerprint', 120)->nullable()->index()->after('session_id');
            $table->foreignId('book_id')->nullable()->after('guest_fingerprint')->constrained('books')->nullOnDelete();
            $table->string('action_details', 255)->nullable()->after('path');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->string('guest_fingerprint', 120)->nullable()->index()->after('user_id');
            $table->string('country_code', 5)->nullable()->index()->after('guest_fingerprint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('traffic_logs', function (Blueprint $table) {
            $table->dropForeign(['book_id']);
            $table->dropColumn([
                'country_code',
                'country_name',
                'country_flag',
                'guest_fingerprint',
                'book_id',
                'action_details',
            ]);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'guest_fingerprint',
                'country_code',
            ]);
        });
    }
};
