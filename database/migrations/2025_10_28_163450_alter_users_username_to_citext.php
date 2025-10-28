<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Activar la extensión citext
        DB::statement('CREATE EXTENSION IF NOT EXISTS citext;');

        // Cambiar la columna username a citext
        DB::statement('ALTER TABLE users ALTER COLUMN username TYPE citext;');

        // Asegurar el índice único (en citext sigue siendo case-insensitive)
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS users_username_unique ON users (username);');
    }

    public function down(): void
    {
        // Eliminar índice y volver a text
        DB::statement('DROP INDEX IF EXISTS users_username_unique;');
        DB::statement('ALTER TABLE users ALTER COLUMN username TYPE text;');
    }
};
