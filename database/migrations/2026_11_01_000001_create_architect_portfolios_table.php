<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('architect_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();           // arsitek (partner)
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 30)->index();            // interior | kitchen_set | full_house | facade
            $table->string('architectural_style', 30)->index(); // minimalist | modern | japandi | industrial
            $table->unsignedBigInteger('estimated_rab')->index();
            $table->decimal('area_sqm', 10, 2);
            $table->unsignedBigInteger('cost_per_sqm');
            $table->text('description')->nullable();
            $table->boolean('is_ready_to_build')->default(false)->index();
            $table->unsignedBigInteger('blueprint_price')->nullable();
            $table->json('blueprint_deliverables')->nullable();
            $table->string('status', 20)->default('draft')->index(); // draft | published | archived
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'category', 'architectural_style'], 'portfolios_filter_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('architect_portfolios');
    }
};
