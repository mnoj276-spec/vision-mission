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
            // Composite Index: Solves multi-variable queries filtering by status, post_type, is_sponsored, and is_featured
            $table->index(
                ['status', 'post_type', 'is_sponsored', 'is_featured', 'published_at'], 
                'idx_job_posts_status_type_monetization_published'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex('idx_job_posts_status_type_monetization_published');
        });
    }
};
