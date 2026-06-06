<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('type', 40)->index();
            $table->string('api_format', 60);
            $table->string('base_url', 500)->nullable();
            $table->text('api_key_encrypted')->nullable();
            $table->json('extra_headers')->nullable();
            $table->json('extra_config')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('priority')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
