<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => (int) $this->id,
            'name'      => (string) $this->name,
            'email'     => (string) $this->email,
            'phone'     => $this->phone ? (string) $this->phone : null,
            'role'      => (string) $this->role,
            'isActive'  => (bool) $this->is_active,
            'createdAt' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
