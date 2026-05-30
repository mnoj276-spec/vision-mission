<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add fingerprint column + unique index to job_posts.
 *
 * Also backfills fingerprints for any existing rows using the same
 * canonical formula as FingerprintService::generate():
 *   SHA256( normalize(title) | department_id | source_url | publish_date )
 *
 * For backfill, source_url is resolved via scraping_logs → scraping_sources.
 * Rows with no matching source log use an empty string for the source segment,
 * producing a deterministic (non-null) fingerprint that satisfies the unique constraint.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            // CHAR(64) — fixed-width SHA-256 hex output, NULL until backfilled.
            $table->char('fingerprint', 64)->nullable()->after('is_historical');
        });

        // Backfill fingerprints for all existing rows in a single pass.
        $this->backfillFingerprints();

        // Apply unique index AFTER backfill to avoid transient constraint violations.
        Schema::table('job_posts', function (Blueprint $table) {
            $table->unique('fingerprint', 'uq_job_posts_fingerprint');
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropUnique('uq_job_posts_fingerprint');
            $table->dropColumn('fingerprint');
        });
    }

    // -------------------------------------------------------------------------

    private function backfillFingerprints(): void
    {
        // Resolve source URLs: map job_post_id → source_url via the scraping_logs table.
        // We only take the first (earliest) successful log entry per job post.
        $sourceMap = DB::table('scraping_logs')
            ->join('scraping_sources', 'scraping_logs.scraping_source_id', '=', 'scraping_sources.id')
            ->where('scraping_logs.status', 'success')
            ->whereNotNull('scraping_logs.job_post_id')
            ->orderBy('scraping_logs.id')
            ->pluck('scraping_sources.source_url', 'scraping_logs.job_post_id');

        // Chunk to keep memory usage constant on large tables.
        DB::table('job_posts')->orderBy('id')->chunk(200, function ($posts) use ($sourceMap) {
            foreach ($posts as $post) {
                $sourceUrl  = $sourceMap[$post->id] ?? '';
                $fingerprint = $this->computeFingerprint(
                    $post->title          ?? '',
                    (string) ($post->department_id ?? ''),
                    $sourceUrl,
                    $post->last_date_to_apply ?? ''
                );

                DB::table('job_posts')
                    ->where('id', $post->id)
                    ->update(['fingerprint' => $fingerprint]);
            }
        });
    }

    /**
     * Inline replica of FingerprintService::generate() — kept self-contained so the
     * migration does not depend on application service classes (which may change).
     */
    private function computeFingerprint(
        string $title,
        string $departmentId,
        string $sourceUrl,
        string $publishDate
    ): string {
        $segments = [
            $this->normalize($title),
            $departmentId,
            $this->normalizeUrl($sourceUrl),
            $this->normalizeDate($publishDate),
        ];

        return hash('sha256', implode('|', $segments));
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}\s\-]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function normalizeUrl(string $url): string
    {
        $url    = trim(strtolower($url));
        $parsed = parse_url($url);
        if (!$parsed) {
            return $url;
        }
        $host = $parsed['host'] ?? '';
        $path = rtrim($parsed['path'] ?? '', '/');
        return $host . $path;
    }

    private function normalizeDate(string $date): string
    {
        if (empty($date)) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception) {
            return strtolower(trim($date));
        }
    }
};
