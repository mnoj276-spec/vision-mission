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
        // 1. Scraping Sources Table (Crawler Configs)
        Schema::create('scraping_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('source_url')->unique();
            $table->enum('source_type', ['rss', 'html', 'table', 'api'])->default('html');
            $table->json('selectors_config')->comment('CSS/XPath selectors or RSS tag paths');
            $table->string('cron_expression')->default('0 0 * * *')->comment('Default execution frequency');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Scraping Logs Table (Audit trail of automation runs)
        Schema::create('scraping_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scraping_source_id')->constrained('scraping_sources')->onDelete('cascade');
            $table->foreignId('job_post_id')->nullable()->constrained('job_posts')->onDelete('set null');
            $table->enum('status', ['success', 'duplicate', 'failed', 'quarantined'])->default('success');
            
            // Rich Auditing Columns for Precision Scraping
            $table->json('raw_payload')->nullable()->comment('Original scraped HTML or raw PDF text parsed');
            $table->json('validation_errors')->nullable()->comment('Descriptive rule failures (e.g. missing deadlines or bad URLs)');
            
            $table->text('error_message')->nullable()->comment('Technical script trace or HTTP fail reason');
            $table->integer('items_found')->default(0);
            $table->timestamps();
            
            // Indexes for fast administrative search
            $table->index(['scraping_source_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraping_logs');
        Schema::dropIfExists('scraping_sources');
    }
};
