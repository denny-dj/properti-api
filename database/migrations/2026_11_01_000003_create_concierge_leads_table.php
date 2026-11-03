<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('concierge_leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_code', 24)->unique();                 // LEAD-2026-XXXX
            $table->string('source', 30)->index();                     // property_detail | architect_portfolio | kpr_calculator
            $table->unsignedBigInteger('reference_id')->nullable();    // id properti / portofolio (sesuai source)
            $table->string('customer_name', 100)->nullable();
            $table->string('customer_phone', 20)->nullable()->index(); // 62812xxxxxxx
            $table->unsignedBigInteger('estimated_budget')->nullable();
            $table->string('payment_preference', 20)->nullable();      // cash | kpr | milestone
            $table->string('status', 20)->default('new')->index();     // new | contacted | surveying | committed | lost
            $table->text('notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['source', 'reference_id', 'customer_phone'], 'leads_dedupe_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('concierge_leads');
    }
};
