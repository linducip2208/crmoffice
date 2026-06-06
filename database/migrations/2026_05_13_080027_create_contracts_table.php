<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('number', 40)->unique();
            $table->string('subject');
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->longText('content');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('contract_value', 15, 2)->nullable();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->string('status', 40)->default('draft');
            $table->char('public_token', 40)->unique();
            $table->dateTime('signed_at')->nullable();
            $table->string('signed_by_name', 180)->nullable();
            $table->text('signed_signature')->nullable();
            $table->string('signed_ip', 45)->nullable();
            $table->integer('notify_expiry_days_before')->default(14);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
