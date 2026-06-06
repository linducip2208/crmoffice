<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('inbound_token', 64)->nullable()->unique()->after('email_pipe');
        });

        Schema::table('lead_sources', function (Blueprint $table) {
            $table->string('form_token', 64)->nullable()->unique()->after('name');
            $table->string('slug', 120)->nullable()->unique()->after('form_token');
        });

        Schema::table('lead_statuses', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('lead_statuses', fn (Blueprint $t) => $t->dropColumn('is_default'));
        Schema::table('lead_sources', function (Blueprint $t) {
            $t->dropColumn(['form_token', 'slug']);
        });
        Schema::table('departments', fn (Blueprint $t) => $t->dropColumn('inbound_token'));
    }
};
