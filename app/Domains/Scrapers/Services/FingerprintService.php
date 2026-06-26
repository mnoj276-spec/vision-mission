<?php

namespace App\Domains\Scrapers\Services;

use App\Models\JobPost;
use Illuminate\Database\Eloquent\Collection;

/**
 * FingerprintService
 *
 * Stateless engine responsible for:
 *   1. Generating a canonical SHA-256 fingerprint for each incoming job record.
 *   2. Detecting fuzzy (near-duplicate) postings via PHP native similar_text().
 *   3. Extracting normalised title variants to catch year-bumped / acronym reposts.
 *
 * Fingerprint formula (all segments lowercased & stripped before hashing):
 *   SHA256( normalize(title) | department_id | source_url | publish_date )
 *
 * Design constraints:
 *   - Pure PHP; no DB calls; no constructor dependencies.
 *   - Safe to call concurrently from multiple queue workers.
 *   - The resulting 64-char hex string fits in a CHAR(64) column with a UNIQUE index,
 *     which acts as the last-resort race-condition guard at the DB layer.
 */
class FingerprintService
{
    /**
     * Default similarity threshold (0–100) for fuzzy duplicate detection.
     * 85 = titles must be at least 85 % identical character-by-character.
     */
    public const DEFAULT_FUZZY_THRESHOLD = 85.0;

    /**
     * How many days back to scan for fuzzy duplicates (keeps the candidate
     * pool small without missing recent re-posts).
     */
    public const FUZZY_LOOKBACK_DAYS = 90;

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Generate a SHA-256 fingerprint from the four canonical identity fields.
     *
     * @param  array  $data  Must contain: title, department_id, source_url, publish_date
     * @return string  64-char lowercase hex string
     */
    public function generate(array $data): string
    {
        $segments = [
            $this->normalize($data['title']          ?? ''),
            (string) ($data['department_id']         ?? ''),
            $this->normalizeUrl($data['source_url']  ?? ''),
            $this->normalizeDate($data['publish_date'] ?? $data['last_date_to_apply'] ?? ''),
        ];

        return hash('sha256', implode('|', $segments));
    }

    /**
     * Normalise a raw title / text segment for consistent hashing and comparison.
     *
     * Steps:
     *   1. Convert to lowercase.
     *   2. Remove all punctuation except hyphens (preserve "10th-pass" etc.).
     *   3. Collapse multiple whitespace chars into a single space.
     *   4. Trim leading/trailing whitespace.
     *
     * @param  string  $text
     * @return string
     */
    public function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^\p{L}\p{N}\s\-]/u', ' ', $text);   // strip punctuation
        $text = preg_replace('/\s+/', ' ', $text);                   // collapse whitespace
        return trim($text);
    }

    /**
     * Find the first existing JobPost whose normalised title is similar enough
     * to the incoming candidate title.
     *
     * Uses PHP native similar_text() — no DB extension required.
     * Candidate pool is limited to the same department + recent posts.
     *
     * @param  string      $candidateTitle  The incoming (to-be-inserted) title
     * @param  Collection  $recentPosts     Pre-fetched Collection of JobPost models
     * @param  float       $threshold       0–100 similarity score required to flag as duplicate
     * @return array|null  ['post' => JobPost, 'score' => float] or null if no match
     */
    public function isFuzzyDuplicate(
        string $candidateTitle,
        Collection $recentPosts,
        float $threshold = self::DEFAULT_FUZZY_THRESHOLD
    ): ?array {
        $normCandidate = $this->normalize($candidateTitle);

        foreach ($recentPosts as $post) {
            $normExisting = $this->normalize($post->title);

            similar_text($normCandidate, $normExisting, $percent);

            if ($percent >= $threshold) {
                return [
                    'post'  => $post,
                    'score' => round($percent, 2),
                ];
            }
        }

        return null;
    }

    /**
     * Detect title-variant duplicates using structural pattern stripping.
     *
     * Variants generated:
     *   1. Year-stripped title  (removes 4-digit years: 2020–2029)
     *   2. Suffix-stripped title (removes trailing keywords: Recruitment, Notification, Vacancy…)
     *   3. Acronym-collapsed    (expands common govt. acronyms for comparison)
     *
     * @param  string      $candidateTitle
     * @param  Collection  $recentPosts
     * @param  float       $threshold       Lowered to 80 for variant matching (more lenient)
     * @return array|null  ['post' => JobPost, 'score' => float, 'variant' => string] or null
     */
    public function detectTitleVariant(
        string $candidateTitle,
        Collection $recentPosts,
        float $threshold = 80.0
    ): ?array {
        $variants = $this->extractTitleVariants($candidateTitle);

        foreach ($recentPosts as $post) {
            $normExisting = $this->normalize($post->title);
            $existingVariants = $this->extractTitleVariants($post->title);

            // Cross-compare all variant pairs
            foreach ($variants as $v1) {
                foreach ($existingVariants as $v2) {
                    similar_text($v1, $v2, $percent);
                    if ($percent >= $threshold) {
                        return [
                            'post'    => $post,
                            'score'   => round($percent, 2),
                            'variant' => $v1,
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Extract a set of normalised title variants for broader matching.
     *
     * @param  string  $title
     * @return string[]
     */
    public function extractTitleVariants(string $title): array
    {
        $norm = $this->normalize($title);
        $variants = [$norm];

        // Variant 1: Strip 4-digit years (2020–2029)
        $yearStripped = trim(preg_replace('/\b20[2-9]\d\b/', '', $norm));
        $yearStripped = preg_replace('/\s+/', ' ', $yearStripped);
        if ($yearStripped !== $norm) {
            $variants[] = trim($yearStripped);
        }

        // Variant 2: Strip common trailing recruitment keywords
        $suffixStripped = preg_replace(
            '/\b(recruitment|notification|vacancy|vacancies|apply online|registration|'
            . 'application|form|online|advertisement|advt|advt\.|post|posts|exam|'
            . 'examination|result|merit list|cutoff|answer key|admit card|hall ticket|'
            . 'syllabus|notice|circular|corrigendum|cancellation|extension)\b/i',
            ' ',
            $norm
        );
        $suffixStripped = trim(preg_replace('/\s+/', ' ', $suffixStripped));
        if ($suffixStripped !== $norm && strlen($suffixStripped) > 5) {
            $variants[] = $suffixStripped;
        }

        // Variant 3: Expand common govt. abbreviations for uniform comparison
        $expanded = $this->expandAcronyms($norm);
        if ($expanded !== $norm) {
            $variants[] = $expanded;
        }

        return array_unique($variants);
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Normalize a URL for consistent hashing (strip trailing slashes, lowercase scheme+host).
     */
    private function normalizeUrl(string $url): string
    {
        $url = trim(strtolower($url));
        $parsed = parse_url($url);
        if (!$parsed) {
            return $url;
        }
        $host = $parsed['host'] ?? '';
        $path = rtrim($parsed['path'] ?? '', '/');
        return $host . $path;
    }

    /**
     * Normalize a date string to YYYY-MM-DD for consistent hashing.
     */
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

    /**
     * Map common government recruitment acronyms to their full forms so that
     * "SSC CGL" and "Staff Selection Commission Combined Graduate Level" can be
     * recognised as the same entity during variant comparison.
     */
    private function expandAcronyms(string $text): string
    {
        $map = [
            '/\bssc\b/i'   => 'staff selection commission',
            '/\bupsc\b/i'  => 'union public service commission',
            '/\brrb\b/i'   => 'railway recruitment board',
            '/\bsbi\b/i'   => 'state bank of india',
            '/\brbi\b/i'   => 'reserve bank of india',
            '/\bias\b/i'   => 'indian administrative service',
            '/\bips\b/i'   => 'indian police service',
            '/\bifs\b/i'   => 'indian foreign service',
            '/\bcgl\b/i'   => 'combined graduate level',
            '/\bchsl\b/i'  => 'combined higher secondary level',
            '/\bgpsc\b/i'  => 'goa public service commission',
            '/\bmpsc\b/i'  => 'maharashtra public service commission',
            '/\btnpsc\b/i' => 'tamil nadu public service commission',
        ];

        return preg_replace(array_keys($map), array_values($map), $text);
    }
}
