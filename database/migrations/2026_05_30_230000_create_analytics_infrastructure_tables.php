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
        // 1. Page Views Table
        Schema::create('analytics_page_views', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('path')->index();
            $table->text('referer')->nullable();
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->boolean('is_bot')->default(false)->index();
            $table->boolean('is_organic')->default(false)->index();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // 2. Job Interactions Table
        Schema::create('analytics_job_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_posts')->onDelete('cascade');
            $table->string('event_type')->index(); // view, apply_click, bookmark, apply_submit
            $table->string('session_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ip_address', 45);
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // 3. Search Queries Table
        Schema::create('analytics_search_queries', function (Blueprint $table) {
            $table->id();
            $table->string('query')->index();
            $table->json('filters')->nullable();
            $table->integer('results_count')->default(0);
            $table->string('session_id')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ip_address', 45);
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // 4. Ad Revenue Events Table
        Schema::create('analytics_revenue_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->index(); // ad_impression, ad_click, premium_sub
            $table->string('slot_name')->index(); // e.g. home_sidebar, job_details_top, global_footer
            $table->decimal('estimated_revenue', 8, 4)->default(0.0000);
            $table->foreignId('job_post_id')->nullable()->constrained('job_posts')->onDelete('set null');
            $table->string('session_id')->index();
            $table->string('ip_address', 45);
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_revenue_events');
        Schema::dropIfExists('analytics_search_queries');
        Schema::dropIfExists('analytics_job_events');
        Schema::dropIfExists('analytics_page_views');
    }
};
