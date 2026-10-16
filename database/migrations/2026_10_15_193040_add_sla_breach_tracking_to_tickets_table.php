<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('sla_first_response_notified_at')->nullable()->after('first_response_due_at');
            $table->timestamp('sla_resolve_notified_at')->nullable()->after('resolve_due_at');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index('first_response_at');
            $table->index('resolve_due_at');
            $table->index('resolved_at');
            $table->index('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['first_response_at']);
            $table->dropIndex(['resolve_due_at']);
            $table->dropIndex(['resolved_at']);
            $table->dropIndex(['closed_at']);
            $table->dropColumn(['sla_first_response_notified_at', 'sla_resolve_notified_at']);
        });
    }
};
