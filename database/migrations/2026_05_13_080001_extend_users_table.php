<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 40)->nullable()->after('email');
            $table->string('job_title', 120)->nullable()->after('phone');
            $table->decimal('hourly_rate', 15, 2)->nullable()->after('job_title');
            $table->unsignedBigInteger('avatar_file_id')->nullable()->after('hourly_rate');
            $table->boolean('is_active')->default(true)->after('avatar_file_id');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->string('locale', 10)->default('en');
            $table->string('timezone', 60)->default('UTC');
            $table->softDeletes();
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'job_title', 'hourly_rate', 'avatar_file_id', 'is_active', 'two_factor_secret', 'two_factor_recovery_codes', 'last_login_at', 'last_login_ip', 'locale', 'timezone', 'deleted_at']);
        });
    }
};
