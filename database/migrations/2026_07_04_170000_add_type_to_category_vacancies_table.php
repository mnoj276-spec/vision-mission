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
        Schema::table('category_vacancies', function (Blueprint $table) {
            $table->string('type', 50)->default('caste_category')->after('category_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category_vacancies', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
