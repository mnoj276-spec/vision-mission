<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            // Add tracking column for historical backfills
            $table->boolean('is_historical')->default(false)->after('is_featured');
            
            // Add composite performance index for rapid duplicate checking during crawler ingestion
            $table->index(['department_id', 'last_date_to_apply'], 'idx_jobs_dup_check');
            $table->index('title', 'idx_jobs_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex('idx_jobs_dup_check');
            $table->dropIndex('idx_jobs_title');
            $table->dropColumn('is_historical');
        });
    }
};
