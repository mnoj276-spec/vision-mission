<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add per-source health tracking columns for the observability dashboard.
     * These are denormalized snapshots updated after each crawl run.
     * The full history remains in scraping_logs.
     */
    public function up(): void
    {
        Schema::table('scraping_sources', function (Blueprint $table) {
            $table->timestamp('last_attempted_at')->nullable()->after('next_run_at');
            $table->timestamp('last_succeeded_at')->nullable()->after('last_attempted_at');
            $table->timestamp('last_failed_at')->nullable()->after('last_succeeded_at');
            $table->text('last_failure_reason')->nullable()->after('last_failed_at');
            $table->unsignedInteger('consecutive_failures')->default(0)->after('last_failure_reason');
            $table->unsignedInteger('last_records_found')->default(0)->after('consecutive_failures');
            $table->unsignedInteger('last_records_published')->default(0)->after('last_records_found');
            $table->string('health_status', 20)->default('inactive')->after('last_records_published');

            $table->index('health_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scraping_sources', function (Blueprint $table) {
            $table->dropIndex(['health_status']);
            $table->dropColumn([
                'last_attempted_at',
                'last_succeeded_at',
                'last_failed_at',
                'last_failure_reason',
                'consecutive_failures',
                'last_records_found',
                'last_records_published',
                'health_status',
            ]);
        });
    }
};
