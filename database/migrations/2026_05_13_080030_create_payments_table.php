<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->string('method', 40);
            $table->foreignId('provider_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->string('transaction_id', 120)->nullable()->index();
            $table->dateTime('paid_at');
            $table->text('note')->nullable();
            $table->string('status', 40)->default('completed');
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
