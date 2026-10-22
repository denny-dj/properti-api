<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();

            // property:{id} | service:{id} | direct  -> satu percakapan per (buyer, partner, konteks)
            $table->string('context_key', 40);
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('architect_service_id')->nullable()->constrained('architect_services')->nullOnDelete();

            // Penanda "sudah dibaca" berbasis ID pesan (bebas masalah presisi detik)
            $table->unsignedBigInteger('buyer_last_read_id')->nullable();
            $table->unsignedBigInteger('partner_last_read_id')->nullable();

            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['buyer_id', 'partner_id', 'context_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
