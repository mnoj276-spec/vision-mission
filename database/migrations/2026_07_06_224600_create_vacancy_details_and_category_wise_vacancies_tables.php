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
        Schema::create('vacancy_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_posts')->onDelete('cascade');
            $table->string('post_name', 255);
            $table->integer('total_post');
            $table->text('eligibility');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('job_post_id');
        });

        Schema::create('category_wise_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained('job_posts')->onDelete('cascade');
            $table->string('post_name', 255);
            $table->integer('ur')->default(0);
            $table->integer('ews')->default(0);
            $table->integer('ebc')->default(0);
            $table->integer('bc')->default(0);
            $table->integer('bc_female')->default(0);
            $table->integer('sc')->default(0);
            $table->integer('st')->default(0);
            $table->integer('total')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('job_post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_wise_vacancies');
        Schema::dropIfExists('vacancy_details');
    }
};
