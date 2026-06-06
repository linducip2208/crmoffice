<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('task_checklist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->string('item');
            $table->boolean('is_done')->default(false);
            $table->integer('order')->default(0);
            $table->timestamp('done_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_checklist');
    }
};
