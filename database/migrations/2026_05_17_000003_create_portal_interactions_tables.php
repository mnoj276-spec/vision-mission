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
        // 1. Bookmarks Table
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('job_post_id')->constrained('job_posts')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'job_post_id']);
        });

        // 2. Job Applications Table
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('job_post_id')->constrained('job_posts')->onDelete('cascade');
            $table->string('resume_path')->nullable();
            $table->enum('status', ['applied', 'reviewing', 'shortlisted', 'rejected'])->default('applied');
            $table->timestamps();
            $table->unique(['user_id', 'job_post_id']);
        });

        // 3. Advertisements Table
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('slot_name')->unique()->comment('e.g., home_sidebar, job_details_top, global_footer');
            $table->text('ad_code')->nullable()->comment('Google AdSense or custom scripts');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Banners Table (Sliders)
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_path');
            $table->string('target_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        // 5. Audit Logs Table (Admin activity tracker)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('action')->comment('e.g., approve_job, toggle_scraper, edit_seo');
            $table->text('details')->nullable()->comment('JSON description of before/after updates');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('advertisements');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('bookmarks');
    }
};
