<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('provider_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('key', 120);
            $table->text('value_encrypted')->nullable();
            $table->boolean('is_secret')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_credentials');
    }
};
