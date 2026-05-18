<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create duplicate_audit_logs table.
 *
 * Dedicated audit table that records every duplicate detection event with full
 * forensic detail — kept separate from scraping_logs to allow independent querying,
 * admin review workflows, and future merge tooling without polluting the general
 * scrape-run log.
 *
 * detection_method enum:
 *   fingerprint  — exact SHA-256 collision (fastest path)
 *   fuzzy        — similar_text() ≥ 85% similarity on normalised titles
 *   title_variant — year-stripped / suffix-stripped / acronym-expanded variant match
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duplicate_audit_logs', function (Blueprint $table) {
            $table->id();

            // The incoming (rejected) job post ID — null if the dupe was caught before
            // the record was ever inserted (most common case).
            $table->unsignedBigInteger('job_post_id')->nullable();

            // The existing master record that the incoming job clashed with.
            $table->unsignedBigInteger('master_job_post_id')->nullable();

            // Which stage of the 3-stage gate caught the duplicate.
            $table->enum('detection_method', ['fingerprint', 'fuzzy', 'title_variant']);

            // Populated for fuzzy and title_variant detections (0.00–100.00).
            $table->decimal('similarity_score', 5, 2)->nullable();

            // Full fingerprint of the incoming payload (always set).
            $table->char('incoming_fingerprint', 64);

            // Fingerprint of the master record (null for fingerprint-exact matches
            // where the master is identified by the fingerprint itself).
            $table->char('master_fingerprint', 64)->nullable();

            // Full raw scraped payload that was rejected — enables re-processing.
            $table->json('raw_payload')->nullable();

            // Set by admin once the duplicate has been reviewed / resolved.
            $table->timestamp('resolved_at')->nullable();

            // Standard timestamps (created_at = detection time).
            $table->timestamps();

            // Foreign keys — use nullable constrained to survive cascade deletes
            // on job_posts without orphaning the audit record.
            $table->foreign('job_post_id')
                  ->references('id')->on('job_posts')
                  ->onDelete('set null');

            $table->foreign('master_job_post_id')
                  ->references('id')->on('job_posts')
                  ->onDelete('set null');

            // Performance indexes for the admin review dashboard.
            $table->index('detection_method', 'idx_dup_logs_method');
            $table->index('incoming_fingerprint', 'idx_dup_logs_incoming_fp');
            $table->index(['master_job_post_id', 'detection_method'], 'idx_dup_logs_master');
            $table->index('resolved_at', 'idx_dup_logs_resolved');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duplicate_audit_logs');
    }
};
