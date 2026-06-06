<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 40)->unique();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('estimate_id')->nullable()->constrained('estimates')->nullOnDelete();
            $table->unsignedBigInteger('recurring_parent_id')->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->index();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_total', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->string('status', 40)->default('draft')->index();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_period', 20)->nullable();
            $table->integer('recurring_count')->nullable();
            $table->integer('recurring_remaining')->nullable();
            $table->date('next_recurring_date')->nullable()->index();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->char('public_token', 40)->unique();
            $table->foreignId('pdf_file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('recurring_parent_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
