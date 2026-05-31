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
            // Composite Index 1: Solves paginated search listings sorted by sponsored, featured, and date
            $table->index(['status', 'is_sponsored', 'is_featured', 'published_at'], 'idx_job_posts_published_sort');

            // Composite Index 2: Solves post-type specific listings (latest jobs, admit cards, results) on homepage
            $table->index(['status', 'post_type', 'published_at'], 'idx_job_posts_status_type_date');

            // Index 3: Accelerates title-based prefix search matching for autocomplete suggestions
            $table->index('title', 'idx_job_posts_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex('idx_job_posts_title');
            $table->dropIndex('idx_job_posts_status_type_date');
            $table->dropIndex('idx_job_posts_published_sort');
        });
    }
};
