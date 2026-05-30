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
        Schema::create('job_post_ai_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')
                  ->unique()
                  ->constrained('job_posts')
                  ->onDelete('cascade');
            
            $table->string('provider'); // openai, gemini, claude
            $table->mediumText('summary')->nullable();
            $table->mediumText('eligibility')->nullable();
            $table->mediumText('selection_process')->nullable();
            $table->json('faqs')->nullable(); // [{"question": "...", "answer": "..."}]
            
            // Hand-crafted SEO optimization overrides
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('schema_content')->nullable(); // JobPosting Schema overrides

            // Approval Workflow state
            $table->string('status')->default('pending'); // pending, approved, rejected
            
            // Queue telemetry & debugging
            $table->text('error_message')->nullable();
            
            $table->timestamps();

            // Indexes for fast administrative filtration
            $table->index(['status', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_post_ai_contents');
    }
};
