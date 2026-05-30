<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => (int) $this->id,
            'status'      => (string) $this->status,
            'resumePath'  => $this->resume_path ? asset('storage/' . $this->resume_path) : null,
            'appliedAt'   => $this->created_at ? $this->created_at->toIso8601String() : null,
            'jobPost'     => $this->relationLoaded('jobPost') && $this->jobPost ? [
                'id'    => (int) $this->jobPost->id,
                'title' => (string) $this->jobPost->title,
                'slug'  => (string) $this->jobPost->slug,
            ] : ($this->jobPost ? [
                'id'    => (int) $this->jobPost->id,
                'title' => (string) $this->jobPost->title,
                'slug'  => (string) $this->jobPost->slug,
            ] : null),
        ];
    }
}
