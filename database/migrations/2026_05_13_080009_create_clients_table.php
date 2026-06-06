<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('industry', 120)->nullable();
            $table->string('website')->nullable();
            $table->string('phone', 40)->nullable();
            $table->text('billing_address')->nullable();
            $table->string('billing_city', 120)->nullable();
            $table->string('billing_state', 120)->nullable();
            $table->char('billing_country', 2)->nullable();
            $table->string('billing_postal', 20)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city', 120)->nullable();
            $table->string('shipping_state', 120)->nullable();
            $table->char('shipping_country', 2)->nullable();
            $table->string('shipping_postal', 20)->nullable();
            $table->string('tax_id', 60)->nullable();
            $table->foreignId('account_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('default_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->string('default_language', 10)->default('en');
            $table->string('status', 40)->default('active')->index();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('company_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
