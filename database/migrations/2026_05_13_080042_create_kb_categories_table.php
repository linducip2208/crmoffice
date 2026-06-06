<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kb_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 180);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('kb_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_categories');
    }
};
