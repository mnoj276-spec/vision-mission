<?php

namespace App\Http\Controllers\Api\V1\Search;

use App\Http\Controllers\Controller;
use App\Domains\Jobs\Services\Contracts\SearchServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        protected SearchServiceInterface $searchService
    ) {}

    /**
     * Provide autocomplete suggestions based on keyword prefixes.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        
        $suggestions = $this->searchService->getAutocompleteSuggestions($query);

        return $this->successResponse(
            $suggestions,
            'Autocomplete suggestions retrieved successfully.'
        );
    }

    /**
     * Detect and provide spelling typo suggestions for search terms.
     */
    public function typoCorrection(Request $request): JsonResponse
    {
        $query = $request->query('q', '');

        $suggestion = $this->searchService->getSpellCorrection($query);

        return $this->successResponse([
            'suggestion' => $suggestion
        ], 'Typo correction suggestion checked successfully.');
    }
}
