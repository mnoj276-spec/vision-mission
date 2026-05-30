<?php

namespace App\Domains\Jobs\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SearchServiceInterface
{
    /**
     * Search job postings with scoring/relevance, filters, pagination, and caching.
     */
    public function searchJobs(array $filters, int $perPage = 10): LengthAwarePaginator;

    /**
     * Generate autocomplete suggestions for a given input query.
     */
    public function getAutocompleteSuggestions(string $query): array;

    /**
     * Compute typo corrections ("Did you mean...") based on Levenshtein distance.
     */
    public function getSpellCorrection(string $query): ?string;

    /**
     * Rebuild the vocabulary dictionary cached for spell check.
     */
    public function rebuildVocabulary(): void;
}
