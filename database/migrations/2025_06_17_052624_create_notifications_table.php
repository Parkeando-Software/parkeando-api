<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Asegurar PostGIS
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            // Quien libera la plaza
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            // Quien se asigna la plaza (nullable si nadie la ocupa)
            $table->foreignId('assigned_to_user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Cuándo se asignó (sirve para auto-expirar "assigned" si se olvidan)
            $table->timestamp('assigned_at')->nullable();

            // Duración anunciada (minutos)
            $table->unsignedTinyInteger('in_minutes');

            // 🔵 Nueva columna: indica si la plaza pertenece a una zona azul (controlada)
            $table->boolean('blue_zone')->default(false);

            // Estados del ciclo
            $table->enum('status', ['active', 'assigned', 'occupied', 'expired'])->default('active');

            $table->timestamps();

            // Índices BTREE
            $table->index('status', 'notifications_status_idx');
            $table->index('user_id', 'notifications_user_idx');
            $table->index('assigned_to_user_id', 'notifications_assigned_to_idx');
            $table->index('created_at', 'notifications_created_idx');
            $table->index(['status', 'assigned_at'], 'notifications_status_assigned_at_idx');
        });

        // Columna geográfica + índice espacial (PostGIS)
        DB::statement("ALTER TABLE notifications ADD COLUMN location geography(Point,4326)");
        DB::statement("CREATE INDEX notifications_location_gix ON notifications USING GIST (location)");

        // (Opcional pero MUY recomendado) ÚNICA pendiente por usuario: active/assigned
        DB::statement("
            CREATE UNIQUE INDEX notifications_one_pending_per_user_idx
            ON notifications (user_id)
            WHERE status IN ('active','assigned')
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Limpia índices creados manualmente
        DB::statement("DROP INDEX IF EXISTS notifications_location_gix");
        DB::statement("DROP INDEX IF EXISTS notifications_one_pending_per_user_idx");

        Schema::dropIfExists('notifications');
    }
};
