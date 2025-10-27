<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('role_id')
                  ->constrained('roles')
                  ->cascadeOnDelete();

            $table->string('username', 30); // requerido
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password');

            $table->boolean('accept_terms')->default(false);
            $table->boolean('account_activated')->default(false);
            $table->string('phone')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        // ✅ Índice único funcional para unicidad case-insensitive
        DB::statement('CREATE UNIQUE INDEX users_username_lower_unique ON users (LOWER(username))');

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // eliminar primero el índice funcional
        DB::statement('DROP INDEX IF EXISTS users_username_lower_unique');

        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
