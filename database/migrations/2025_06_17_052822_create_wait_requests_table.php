<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wait_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('notification_id')
                  ->constrained('notifications')
                  ->onDelete('cascade');

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->enum('status', ['pending', 'accepted', 'rejected'])
                  ->default('pending');

            $table->timestamps();

            // Índices “normales”
            $table->index(['notification_id', 'status'], 'wait_requests_notification_status_idx');
            $table->index('user_id', 'wait_requests_user_idx');

            // Evitar que un mismo usuario duplique solicitud sobre la misma notificación
            $table->unique(['notification_id', 'user_id'], 'wait_requests_unique_notification_user');
        });

        
        // Asegura que SOLO exista una solicitud 'accepted' por notificación
        DB::statement("
            CREATE UNIQUE INDEX wait_requests_one_accepted_per_notification
            ON wait_requests (notification_id)
            WHERE status = 'accepted'
        ");
    }

    public function down(): void
    {
        // Borrar el índice parcial antes de soltar la tabla
        DB::statement("DROP INDEX IF EXISTS wait_requests_one_accepted_per_notification");
        Schema::dropIfExists('wait_requests');
    }
};
