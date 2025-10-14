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
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
        $table->string('provider_id');
        $table->decimal('amount', 8, 2);
        $table->string('currency', 10)->default('EUR');
        $table->enum('status', ['pending', 'successful', 'failed'])->default('pending');
        $table->enum('payment_method', ['card', 'paypal', 'apple_pay', 'google_pay'])->nullable();
        $table->dateTime('paid_at')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
