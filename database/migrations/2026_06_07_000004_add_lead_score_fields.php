<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->integer('lead_score')->nullable()->comment('AI/rule-based lead score 0-100');
            $table->string('lead_score_level', 20)->nullable()->comment('hot, warm, cold');
            $table->json('lead_score_factors')->nullable()->comment('Scoring factor breakdown');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['lead_score', 'lead_score_level', 'lead_score_factors']);
        });
    }
};
