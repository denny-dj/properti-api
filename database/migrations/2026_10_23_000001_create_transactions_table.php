<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique(); // dipakai sebagai order_id di payment gateway
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();                        // pembayar
            $table->foreignId('payee_id')->nullable()->constrained('users')->nullOnDelete();      // penerima (partner)

            $table->string('type', 30)->index();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('architect_booking_id')->nullable()->constrained('architect_bookings')->nullOnDelete();

            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('IDR');
            $table->string('status', 20)->default('pending')->index();

            $table->string('gateway', 20)->default('midtrans');
            $table->string('snap_token')->nullable();
            $table->text('redirect_url')->nullable();
            $table->string('payment_type', 40)->nullable();     // bank_transfer, credit_card, gopay, qris, ...
            $table->string('payment_channel', 40)->nullable();  // bca, bni, mandiri, ...
            $table->unsignedTinyInteger('installment_term')->nullable(); // cicilan kartu kredit (bulan)
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->string('fraud_status', 20)->nullable();

            $table->boolean('needs_review')->default(false)->index(); // anomali yang perlu dicek admin
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->json('gateway_payload')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index(['architect_booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
