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
            $table->string('post_type')->default('job')->after('category_id');
            $table->index('post_type', 'idx_job_posts_post_type');
            $table->index(['status', 'post_type', 'published_at'], 'idx_job_posts_status_type_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex('idx_job_posts_status_type_published');
            $table->dropIndex('idx_job_posts_post_type');
            $table->dropColumn('post_type');
        });
    }
};
