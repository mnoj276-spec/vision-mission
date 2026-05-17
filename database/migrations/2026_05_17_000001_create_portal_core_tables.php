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
        // 1. Categories Table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Departments Table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->timestamps();
        });

        // 3. States Table
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->timestamps();
        });

        // 4. Qualifications Table
        Schema::create('qualifications', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // 5. Tags Table
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // 6. Job Posts Table (Renamed from jobs to prevent queue collisions)
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->onDelete('restrict');
            $table->foreignId('state_id')->constrained('states')->onDelete('restrict');
            $table->foreignId('qualification_id')->constrained('qualifications')->onDelete('restrict');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('exam_pattern')->nullable();
            $table->text('selection_process')->nullable();
            
            $table->string('age_limit')->nullable();
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->integer('vacancy_count')->default(0);
            $table->decimal('application_fee', 8, 2)->default(0.00);
            
            $table->string('official_website_link', 500)->nullable();
            $table->string('apply_link', 500)->nullable();
            $table->string('notification_pdf_path', 500)->nullable();
            
            $table->date('last_date_to_apply')->nullable()->index();
            $table->date('exam_date')->nullable();
            
            $table->enum('status', ['draft', 'scheduled', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
            
            // Composite Index for AJAX Filter Queries and search layouts
            $table->index(['status', 'published_at', 'is_featured']);
        });

        // 7. Job Post Tags Table (Pivot Table)
        Schema::create('job_post_tags', function (Blueprint $table) {
            $table->foreignId('job_post_id')->constrained('job_posts')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['job_post_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_post_tags');
        Schema::dropIfExists('job_posts');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('qualifications');
        Schema::dropIfExists('states');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('categories');
    }
};
