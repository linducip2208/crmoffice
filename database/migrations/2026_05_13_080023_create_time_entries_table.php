<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->integer('minutes')->nullable();
            $table->decimal('hourly_rate', 15, 2)->nullable();
            $table->boolean('is_billable')->default(false);
            $table->boolean('is_invoiced')->default(false);
            $table->unsignedBigInteger('invoice_item_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['is_billable', 'is_invoiced']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
