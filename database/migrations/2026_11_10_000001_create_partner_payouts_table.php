<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('reference', 120)->nullable();   // no. bukti transfer / referensi bank
            $table->text('note')->nullable();
            $table->timestamp('paid_at');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['partner_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_payouts');
    }
};
