<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('architect_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('architect_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // ISO-8601: 1 = Senin ... 7 = Minggu
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->index(['architect_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('architect_availabilities');
    }
};
