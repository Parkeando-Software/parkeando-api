<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_inbox', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->index();
            $t->string('type');          // 'assigned_notice' | 'points_awarded' | ...
            $t->json('payload');         // datos del toast
            $t->boolean('is_read')->default(false)->index();
            $t->timestamps();

            $t->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_inbox');
    }
};
