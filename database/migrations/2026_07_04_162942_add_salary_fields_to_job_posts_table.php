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
            $table->string('salary_grade', 100)->nullable()->after('salary_max');
            $table->string('pay_level', 100)->nullable()->after('salary_grade');
            $table->string('pay_matrix', 255)->nullable()->after('pay_level');
            $table->string('pay_scale', 255)->nullable()->after('pay_matrix');
            $table->string('stipend', 255)->nullable()->after('pay_scale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn([
                'salary_grade',
                'pay_level',
                'pay_matrix',
                'pay_scale',
                'stipend',
            ]);
        });
    }
};
