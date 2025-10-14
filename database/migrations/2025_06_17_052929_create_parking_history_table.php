<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Asegurar PostGIS
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');

        // Crear tabla base
        Schema::create('parking_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->enum('type', ['released', 'occupied']);

            // timestamps
            $table->timestamps();

            // Índices BTREE útiles
            $table->index('user_id', 'parking_history_user_idx');
            $table->index('type', 'parking_history_type_idx');
            $table->index('created_at', 'parking_history_created_idx');

            // Índice compuesto (muy útil para listados por usuario y fecha)
            $table->index(['user_id', 'created_at'], 'parking_history_user_created_idx');
        });

        // Columna geográfica + índice espacial
        // NOT NULL porque en tu flujo siempre se construye 'location' vía mutators
        DB::statement("ALTER TABLE parking_history ADD COLUMN location geography(Point,4326) NOT NULL");
        DB::statement("CREATE INDEX parking_history_location_gix ON parking_history USING GIST (location)");
    }

    public function down(): void
    {
        Schema::dropIfExists('parking_history');
        // Nota: no se elimina la extensión postgis en down(), para no afectar a otras tablas.
    }
};
