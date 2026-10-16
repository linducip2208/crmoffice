<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('invoice_date');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('paid_at');
            $table->index('status');
        });

        Schema::table('time_entries', function (Blueprint $table) {
            $table->index('start_at');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index('expense_date');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('converted_at');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index('completed_at');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index('deadline');
        });

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->index('reminder_sent_at');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['invoice_date']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['paid_at']);
            $table->dropIndex(['status']);
        });

        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropIndex(['start_at']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['expense_date']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['converted_at']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['completed_at']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['deadline']);
        });

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropIndex(['reminder_sent_at']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex(['is_published', 'published_at']);
        });
    }
};
