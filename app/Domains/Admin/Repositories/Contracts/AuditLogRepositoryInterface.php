<?php

namespace App\Domains\Admin\Repositories\Contracts;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * AuditLogRepositoryInterface
 *
 * Abstracts all persistence operations for admin audit logs,
 * following the Repository Pattern and Dependency Inversion Principle.
 */
interface AuditLogRepositoryInterface
{
    /**
     * Create a new audit log entry.
     */
    public function create(array $data): AuditLog;

    /**
     * Retrieve paginated audit logs ordered by most recent.
     */
    public function getPaginated(int $perPage = 10): LengthAwarePaginator;
}
