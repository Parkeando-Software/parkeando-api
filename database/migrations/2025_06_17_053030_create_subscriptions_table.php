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
    Schema::create('subscriptions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
        $table->enum('type', ['monthly', 'quarterly', 'annual']);
        $table->enum('payment_method', ['credit_card', 'paypal', 'apple_pay', 'google_pay'])->nullable();
        $table->date('start_date');
        $table->date('end_date');
        $table->boolean('active')->default(true);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
