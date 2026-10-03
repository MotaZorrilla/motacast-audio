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
        Schema::create('traffic_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_hash', 64)->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id', 64)->nullable()->index();
            $table->string('path', 255)->index();
            $table->string('method', 10)->default('GET');
            $table->smallInteger('status_code')->default(200);
            $table->string('referer', 500)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 20)->default('desktop')->index();
            $table->boolean('is_crawler')->default(false)->index();
            $table->timestamp('created_at')->useCurrent()->index();

            // Composite indexes for common telemetry aggregation queries
            $table->index(['created_at', 'is_crawler']);
            $table->index(['created_at', 'user_id']);
            $table->index(['created_at', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traffic_logs');
    }
};
