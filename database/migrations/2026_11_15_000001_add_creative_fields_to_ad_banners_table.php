<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Teks promo untuk hero beranda. Semua opsional; tampilan tetap berfungsi bila kosong. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_banners', function (Blueprint $table) {
            if (! Schema::hasColumn('ad_banners', 'subtitle')) {
                $table->string('subtitle', 200)->nullable()->after('title');
            }
            if (! Schema::hasColumn('ad_banners', 'price_label')) {
                $table->string('price_label', 120)->nullable()->after('subtitle');   // mis. "Mulai Rp 850 Jt · Unit terbatas"
            }
            if (! Schema::hasColumn('ad_banners', 'badge_label')) {
                $table->string('badge_label', 60)->nullable()->after('price_label'); // mis. "Featured Developer"
            }
        });
    }

    public function down(): void
    {
        Schema::table('ad_banners', function (Blueprint $table) {
            foreach (['subtitle', 'price_label', 'badge_label'] as $column) {
                if (Schema::hasColumn('ad_banners', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
