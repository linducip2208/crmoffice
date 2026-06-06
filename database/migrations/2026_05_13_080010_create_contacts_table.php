<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('first_name', 120);
            $table->string('last_name', 120)->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone', 40)->nullable();
            $table->string('position', 120)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('portal_access')->default(false);
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->string('invitation_token', 64)->nullable();
            $table->timestamp('invitation_expires_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('receives_invoice_emails')->default(true);
            $table->boolean('receives_ticket_emails')->default(true);
            $table->boolean('receives_project_emails')->default(true);
            $table->string('locale', 10)->default('en');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
