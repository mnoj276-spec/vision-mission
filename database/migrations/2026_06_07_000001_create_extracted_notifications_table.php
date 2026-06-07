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
        Schema::create('extracted_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('file_path', 500)->nullable();
            $table->string('original_filename', 255)->nullable();
            $table->string('file_type', 50);
            $table->longText('raw_text')->nullable();
            $table->json('extracted_data')->nullable();
            $table->string('validation_status', 30)->default('pending'); // pending, valid, invalid
            $table->json('validation_errors')->nullable();
            $table->string('status', 30)->default('pending'); // pending, processing, processed, failed, approved
            $table->foreignId('job_post_id')->nullable()->constrained('job_posts')->onDelete('set null');
            $table->timestamps();

            // Indexes for lookup performance
            $table->index('status');
            $table->index('validation_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracted_notifications');
    }
};
