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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guest_email')->nullable();
            $table->string('type')->default('consulta'); // 'consulta', 'bug', 'sugerencia', 'extension_limite'
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('pendiente'); // 'pendiente', 'en_revision', 'resuelto', 'rechazado'
            $table->integer('requested_books')->nullable();
            $table->text('admin_reply')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
