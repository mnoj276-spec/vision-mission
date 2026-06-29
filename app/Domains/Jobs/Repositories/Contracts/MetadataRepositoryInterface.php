<?php

namespace App\Domains\Jobs\Repositories\Contracts;

use App\Models\Category;
use App\Models\Department;
use App\Models\Qualification;
use App\Models\State;
use Illuminate\Database\Eloquent\Collection;

/**
 * MetadataRepositoryInterface
 *
 * Abstracts lookup data retrieval (State, Category, Department, Qualification)
 * so that services never call Eloquent models directly — enforcing strict
 * Repository Pattern boundaries per DDD.
 */
interface MetadataRepositoryInterface
{
    /**
     * Get all states.
     */
    public function getAllStates(): Collection;

    /**
     * Get all active categories.
     */
    public function getActiveCategories(): Collection;

    /**
     * Get all qualifications.
     */
    public function getAllQualifications(): Collection;

    /**
     * Get all departments.
     */
    public function getAllDepartments(): Collection;
}
