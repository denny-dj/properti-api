<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Idempoten: tiap kolom hanya ditambahkan bila belum ada. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'platform_fee')) {
                $table->decimal('platform_fee', 15, 2)->default(0)->after('amount');
            }
            if (! Schema::hasColumn('transactions', 'net_partner_amount')) {
                $table->decimal('net_partner_amount', 15, 2)->default(0)->after('platform_fee');
            }
            if (! Schema::hasColumn('transactions', 'deliverable_link')) {
                $table->string('deliverable_link', 500)->nullable();
            }
            if (! Schema::hasColumn('transactions', 'deliverable_submitted_at')) {
                $table->timestamp('deliverable_submitted_at')->nullable();
            }
            if (! Schema::hasColumn('transactions', 'client_confirmed_at')) {
                $table->timestamp('client_confirmed_at')->nullable()->index();
            }
            if (! Schema::hasColumn('transactions', 'portfolio_id')) {
                $table->foreignId('portfolio_id')->nullable()->constrained('architect_portfolios')->nullOnDelete();
            }
            if (! Schema::hasColumn('transactions', 'lead_id')) {
                $table->foreignId('lead_id')->nullable()->constrained('concierge_leads')->nullOnDelete();
            }
            if (! Schema::hasColumn('transactions', 'issued_by')) {
                $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            foreach (['portfolio_id', 'lead_id', 'issued_by'] as $fk) {
                if (Schema::hasColumn('transactions', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }

            $table->dropColumn(array_values(array_filter(
                ['platform_fee', 'net_partner_amount', 'deliverable_link', 'deliverable_submitted_at', 'client_confirmed_at'],
                fn (string $c) => Schema::hasColumn('transactions', $c),
            )));
        });
    }
};
