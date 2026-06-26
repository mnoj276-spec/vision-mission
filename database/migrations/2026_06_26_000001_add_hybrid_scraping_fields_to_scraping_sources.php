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
        Schema::table('scraping_sources', function (Blueprint $table) {
            $table->json('detected_features')->nullable();
            $table->string('preferred_engine')->nullable();
            $table->json('cookies')->nullable();
            $table->json('performance_stats')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scraping_sources', function (Blueprint $table) {
            $table->dropColumn(['detected_features', 'preferred_engine', 'cookies', 'performance_stats']);
        });
    }
};
