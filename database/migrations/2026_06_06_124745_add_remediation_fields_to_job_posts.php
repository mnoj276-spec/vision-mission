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
        // 1. Add fields to job_posts
        Schema::table('job_posts', function (Blueprint $table) {
            $table->string('advertisement_number', 100)->nullable()->after('slug');
            $table->char('pdf_hash', 64)->nullable()->after('notification_pdf_path')->index('idx_job_posts_pdf_hash');
            $table->text('experience_required')->nullable()->after('selection_process');
            $table->date('start_date')->nullable()->after('last_date_to_apply');
            $table->date('result_date')->nullable()->after('exam_date');
        });

        // 2. Create category_vacancies table
        Schema::create('category_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_posts')->onDelete('cascade');
            $table->string('category_name', 100);
            $table->integer('vacancy_count');
            $table->timestamps();

            $table->index(['job_post_id', 'category_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_vacancies');

        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex('idx_job_posts_pdf_hash');
            $table->dropColumn([
                'advertisement_number',
                'pdf_hash',
                'experience_required',
                'start_date',
                'result_date',
            ]);
        });
    }
};
