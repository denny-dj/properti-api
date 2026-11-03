<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('placement', 30)->index();   // top_hero | featured_developer | architect_spotlight
            $table->string('banner_image');
            $table->string('target_url', 500);
            $table->unsignedBigInteger('impression_count')->default(0);
            $table->unsignedBigInteger('click_count')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->index(['placement', 'is_active', 'start_date', 'end_date'], 'banners_live_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_banners');
    }
};
