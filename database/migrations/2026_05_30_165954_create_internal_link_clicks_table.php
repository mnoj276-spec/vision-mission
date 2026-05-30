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
        Schema::create('internal_link_clicks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('source_job_post_id')
                  ->comment('The page the user was viewing when they clicked');
            $table->unsignedBigInteger('target_job_post_id')
                  ->nullable()
                  ->comment('The linked job post (null for category/state links)');
            $table->string('target_url', 500)
                  ->comment('Full target URL for all link types');
            $table->string('link_section', 50)
                  ->comment('Section: related_jobs, related_results, related_admit_cards, categories, state_reco, cross_type');
            $table->string('anchor_text', 255)
                  ->comment('The anchor text displayed to the user');

            $table->timestamp('clicked_at')->useCurrent();
            $table->string('session_id', 40)->nullable();

            $table->timestamps();

            // Indexes for analytics queries
            $table->index('source_job_post_id', 'ilc_source_idx');
            $table->index('target_job_post_id', 'ilc_target_idx');
            $table->index('link_section', 'ilc_section_idx');
            $table->index('clicked_at', 'ilc_clicked_at_idx');
            $table->index(['link_section', 'clicked_at'], 'ilc_section_time_idx');

            $table->foreign('source_job_post_id')
                  ->references('id')->on('job_posts')
                  ->onDelete('cascade');
            $table->foreign('target_job_post_id')
                  ->references('id')->on('job_posts')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_link_clicks');
    }
};
