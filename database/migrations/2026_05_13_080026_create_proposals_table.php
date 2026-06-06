<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->string('number', 40)->unique();
            $table->string('subject');
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->longText('content');
            $table->decimal('total', 15, 2)->default(0);
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->date('open_until')->nullable();
            $table->string('status', 40)->default('draft');
            $table->char('public_token', 40)->unique();
            $table->dateTime('accepted_at')->nullable();
            $table->string('accepted_by_name', 180)->nullable();
            $table->text('accepted_signature')->nullable();
            $table->string('accepted_ip', 45)->nullable();
            $table->dateTime('declined_at')->nullable();
            $table->text('decline_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
