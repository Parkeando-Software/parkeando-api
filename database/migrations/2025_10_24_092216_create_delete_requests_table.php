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
        Schema::create('delete_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_email');
            $table->string('reason', 50);
            $table->text('additional_info')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('confirmation_token')->unique();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('user_email');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delete_requests');
    }
};
