<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Logo studio/bisnis mitra (path pada disk public). Kolom lain sudah cukup untuk pengaturan profil. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('partner_profiles', 'logo')) {
                $table->string('logo')->nullable()->after('portfolio_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('partner_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('partner_profiles', 'logo')) {
                $table->dropColumn('logo');
            }
        });
    }
};
