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
        Schema::create('ai_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scraping_source_id')->nullable()->constrained('scraping_sources')->onDelete('set null');
            $table->text('raw_text');
            $table->json('extracted_json');
            $table->json('confidence_scores');
            $table->decimal('overall_score', 5, 2);
            $table->string('status'); // passed, failed_confidence, failed_api
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_audit_logs');
    }
};
