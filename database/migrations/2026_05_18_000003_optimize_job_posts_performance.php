<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add columns to job_posts
        Schema::table('job_posts', function (Blueprint $table) {
            $table->date('expires_at')->nullable()->after('last_date_to_apply');
            $table->unsignedBigInteger('source_id')->nullable()->after('qualification_id');
            $table->softDeletes()->after('updated_at');
        });

        // 2. Backfill historical data
        // Backfill expires_at from last_date_to_apply
        DB::table('job_posts')->update([
            'expires_at' => DB::raw('last_date_to_apply')
        ]);

        // Backfill source_id from scraping_logs (cross-database compatible subquery)
        DB::table('job_posts')
            ->whereIn('id', function ($query) {
                $query->select('job_post_id')
                    ->from('scraping_logs')
                    ->whereNotNull('job_post_id');
            })
            ->update([
                'source_id' => DB::raw('(SELECT scraping_source_id FROM scraping_logs WHERE scraping_logs.job_post_id = job_posts.id LIMIT 1)')
            ]);

        // 3. Apply Indexes & Foreign Key Constraints
        Schema::table('job_posts', function (Blueprint $table) {
            $table->foreign('source_id')
                ->references('id')
                ->on('scraping_sources')
                ->nullOnDelete();

            $table->index('expires_at', 'idx_job_posts_expires_at');
            $table->index('source_id', 'idx_job_posts_source_id');
            $table->index('deleted_at', 'idx_job_posts_deleted_at');
            $table->index('status', 'idx_job_posts_status_single');
        });

        // 4. Create MySQL Full-Text Search index (only in MySQL production env)
        if (DB::getDriverName() === 'mysql') {
            Schema::table('job_posts', function (Blueprint $table) {
                $table->fullText(['title', 'description'], 'ft_job_posts_title_desc');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                $table->dropFullText('ft_job_posts_title_desc');
            }

            $table->dropForeign(['source_id']);
            $table->dropIndex('idx_job_posts_status_single');
            $table->dropIndex('idx_job_posts_deleted_at');
            $table->dropIndex('idx_job_posts_source_id');
            $table->dropIndex('idx_job_posts_expires_at');

            $table->dropColumn(['expires_at', 'source_id', 'deleted_at']);
        });
    }
};
