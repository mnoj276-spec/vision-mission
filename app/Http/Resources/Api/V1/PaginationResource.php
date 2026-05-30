<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Pagination\LengthAwarePaginator;

class PaginationResource
{
    /**
     * Format LengthAwarePaginator metadata cleanly for mobile clients.
     */
    public static function format(LengthAwarePaginator $paginator): array
    {
        return [
            'currentPage'     => $paginator->currentPage(),
            'totalPages'      => $paginator->lastPage(),
            'perPage'         => $paginator->perPage(),
            'totalCount'      => $paginator->total(),
            'hasNextPage'     => $paginator->hasMorePages(),
            'hasPreviousPage' => !$paginator->onFirstPage(),
        ];
    }
}
