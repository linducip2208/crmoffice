<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kb_article_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('kb_articles')->cascadeOnDelete();
            $table->string('voter_ip', 45);
            $table->boolean('helpful');
            $table->timestamp('voted_at');
            $table->unique(['article_id', 'voter_ip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kb_article_votes');
    }
};
