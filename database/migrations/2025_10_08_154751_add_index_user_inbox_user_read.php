<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('user_inbox', function (Blueprint $table) {
            $table->index(['user_id', 'is_read'], 'idx_user_inbox_user_read');
        });
    }
    public function down(): void {
        Schema::table('user_inbox', function (Blueprint $table) {
            $table->dropIndex('idx_user_inbox_user_read');
        });
    }
};
