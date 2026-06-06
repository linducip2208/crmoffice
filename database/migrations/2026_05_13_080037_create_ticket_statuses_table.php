<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->boolean('is_open')->default(true);
            $table->boolean('is_resolved')->default(false);
            $table->integer('order')->default(0);
            $table->string('color', 7)->default('#3b82f6');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_statuses');
    }
};
