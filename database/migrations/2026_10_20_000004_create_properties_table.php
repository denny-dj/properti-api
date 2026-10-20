<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();

            $table->string('property_type', 20)->index();                 // house | shop
            $table->string('listing_type', 20)->default('sale')->index(); // sale | rent
            $table->decimal('price', 15, 2)->index();

            $table->unsignedTinyInteger('bedrooms')->default(0);
            $table->unsignedTinyInteger('bathrooms')->default(0);
            $table->unsignedTinyInteger('floors')->default(1);
            $table->decimal('land_area', 10, 2)->nullable();     // m2 (luas tanah)
            $table->decimal('building_area', 10, 2)->nullable(); // m2 (luas bangunan)
            $table->string('certificate', 20)->nullable();       // SHM, HGB, dll.

            // Lokasi & peta
            $table->text('address');
            $table->string('district')->nullable();
            $table->string('city')->index();
            $table->string('province')->index();
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Moderasi & status
            $table->string('status', 20)->default('draft')->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);

            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'city', 'property_type']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
