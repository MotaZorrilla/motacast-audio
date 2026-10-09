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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->index()->after('email');
            $table->string('status')->default('active')->index()->after('role');
            $table->integer('book_limit')->default(3)->after('status');
            $table->boolean('auto_extension_used')->default(false)->after('book_limit');
            $table->timestamp('beta_disclaimer_accepted_at')->nullable()->after('auto_extension_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'status',
                'book_limit',
                'auto_extension_used',
                'beta_disclaimer_accepted_at',
            ]);
        });
    }
};
