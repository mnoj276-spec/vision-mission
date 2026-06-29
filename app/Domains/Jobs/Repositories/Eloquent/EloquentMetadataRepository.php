<?php

namespace App\Domains\Jobs\Repositories\Eloquent;

use App\Domains\Jobs\Repositories\Contracts\MetadataRepositoryInterface;
use App\Models\Category;
use App\Models\Department;
use App\Models\Qualification;
use App\Models\State;
use Illuminate\Database\Eloquent\Collection;

/**
 * EloquentMetadataRepository
 *
 * Eloquent implementation of lookup data retrieval.
 * Keeps all direct Model calls isolated to the repository layer.
 */
class EloquentMetadataRepository implements MetadataRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAllStates(): Collection
    {
        return State::all();
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveCategories(): Collection
    {
        return Category::where('is_active', true)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllQualifications(): Collection
    {
        return Qualification::all();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllDepartments(): Collection
    {
        return Department::all();
    }
}
