<?php

namespace App\Domains\Admin\Repositories\Eloquent;

use App\Domains\Admin\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * EloquentAuditLogRepository
 *
 * Eloquent implementation of audit log persistence.
 * Encapsulates all AuditLog database interactions behind the interface.
 */
class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(array $data): AuditLog
    {
        return AuditLog::create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return AuditLog::with('user')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }
}
