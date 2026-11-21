<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->index()->after('error_message');
        });

        // Upgrade existing standard accounts to unlimited books; existing historical books keep expires_at = null
        DB::table('users')
            ->where('role', '!=', 'admin')
            ->update(['book_limit' => -1]);
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['expires_at']);
            $table->dropColumn('expires_at');
        });
    }
};
