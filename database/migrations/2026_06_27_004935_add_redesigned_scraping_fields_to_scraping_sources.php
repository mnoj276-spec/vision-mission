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
            $table->string('priority')->default('default')->after('is_active');
            $table->string('last_modified')->nullable()->after('priority');
            $table->string('etag')->nullable()->after('last_modified');
            $table->integer('crawl_interval_minutes')->default(60)->after('etag');
            $table->timestamp('next_run_at')->nullable()->after('crawl_interval_minutes');
            
            // Add index for scheduled lookup
            $table->index(['is_active', 'next_run_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scraping_sources', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'next_run_at']);
            $table->dropColumn(['priority', 'last_modified', 'etag', 'crawl_interval_minutes', 'next_run_at']);
        });
    }
};
