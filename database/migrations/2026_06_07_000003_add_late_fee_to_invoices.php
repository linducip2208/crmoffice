<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('late_fee_percent', 5, 2)->nullable()->after('balance_due');
            $table->decimal('late_fee_fixed', 15, 2)->nullable()->after('late_fee_percent');
            $table->timestamp('late_fee_charged_at')->nullable()->after('late_fee_fixed');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['late_fee_percent', 'late_fee_fixed', 'late_fee_charged_at']);
        });
    }
};
