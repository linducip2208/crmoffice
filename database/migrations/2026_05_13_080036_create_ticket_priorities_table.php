<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->integer('response_minutes_sla')->nullable();
            $table->integer('resolve_minutes_sla')->nullable();
            $table->string('color', 7)->default('#6b7280');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_priorities');
    }
};
