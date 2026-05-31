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
            $table->boolean('is_sponsored')->default(false)->after('is_featured');
            $table->string('affiliate_link', 500)->nullable()->after('apply_link');
            
            $table->index('is_sponsored', 'idx_job_posts_is_sponsored');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('membership_plan')->default('free')->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('membership_plan');
        });

        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex('idx_job_posts_is_sponsored');
            $table->dropColumn(['is_sponsored', 'affiliate_link']);
        });
    }
};
