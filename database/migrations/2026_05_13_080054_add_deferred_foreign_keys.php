<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->foreign('converted_invoice_id')->references('id')->on('invoices')->nullOnDelete();
        });

        Schema::table('time_entries', function (Blueprint $table) {
            $table->foreign('invoice_item_id')->references('id')->on('invoice_items')->nullOnDelete();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreign('expense_id')->references('id')->on('expenses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['expense_id']);
        });
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropForeign(['invoice_item_id']);
        });
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropForeign(['converted_invoice_id']);
        });
    }
};
