<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('traffic_logs', function (Blueprint $table) {
            $table->index(['created_at', 'ip_hash'], 'traffic_logs_created_at_ip_hash_index');
        });
    }

    public function down(): void
    {
        Schema::table('traffic_logs', function (Blueprint $table) {
            $table->dropIndex('traffic_logs_created_at_ip_hash_index');
        });
    }
};
