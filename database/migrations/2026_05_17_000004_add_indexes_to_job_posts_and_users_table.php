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
            $table->index('state_id', 'idx_job_posts_state');
            $table->index('category_id', 'idx_job_posts_category');
            $table->index('qualification_id', 'idx_job_posts_qualification');
            $table->index('department_id', 'idx_job_posts_department');
            $table->index('salary_max', 'idx_job_posts_salary');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'idx_users_role');
            $table->index('is_active', 'idx_users_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_active');
        });

        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex('idx_job_posts_state');
            $table->dropIndex('idx_job_posts_category');
            $table->dropIndex('idx_job_posts_qualification');
            $table->dropIndex('idx_job_posts_department');
            $table->dropIndex('idx_job_posts_salary');
        });
    }
};
