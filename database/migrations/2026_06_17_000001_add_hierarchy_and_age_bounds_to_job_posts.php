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
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->integer('age_min')->nullable()->after('age_limit');
            $table->integer('age_max')->nullable()->after('age_min');

            $table->foreign('parent_id')->references('id')->on('job_posts')->onDelete('cascade');
            $table->index('parent_id', 'idx_job_posts_parent_id');
            $table->index(['age_min', 'age_max'], 'idx_job_posts_age_bounds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex('idx_job_posts_parent_id');
            $table->dropIndex('idx_job_posts_age_bounds');
            
            $table->dropColumn(['parent_id', 'age_min', 'age_max']);
        });
    }
};
