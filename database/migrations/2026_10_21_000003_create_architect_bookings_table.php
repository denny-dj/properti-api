<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('architect_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 24)->unique();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('architect_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('architect_service_id')->constrained('architect_services')->restrictOnDelete();

            $table->dateTime('scheduled_start');
            $table->dateTime('scheduled_end');
            $table->string('meeting_type', 20)->default('online'); // online | onsite
            $table->text('location')->nullable();
            $table->text('notes')->nullable();

            // Snapshot saat pemesanan (harga layanan bisa berubah kemudian)
            $table->decimal('price', 15, 2);
            $table->unsignedSmallInteger('duration_minutes');

            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['architect_id', 'scheduled_start', 'scheduled_end'], 'bookings_architect_schedule_idx');
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('architect_bookings');
    }
};
