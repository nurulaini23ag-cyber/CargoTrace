<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique()->index();
            $table->foreignId('trade_transaction_id')->constrained('trade_transactions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 14, 2);
            $table->string('currency', 5)->default('USD');
            $table->string('payment_method')->default('VIRTUAL_ACCOUNT'); // VIRTUAL_ACCOUNT, CREDIT_CARD, LETTER_OF_CREDIT, QRIS, BANK_TRANSFER
            $table->string('payment_channel')->nullable(); // Mandiri VA, BCA VA, Visa/Mastercard 3DS, SWIFT MT700, QRIS
            $table->string('payment_status')->default('PENDING'); // PENDING, PROCESSING, SUCCESS, FAILED, CANCELLED
            $table->string('virtual_account_number')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->string('transaction_reference')->unique()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
