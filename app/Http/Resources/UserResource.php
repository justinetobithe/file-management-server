<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'address'    => $this->address,
            'role'       => $this->role,
            'image'      => $this->image,
            'position'   => $this->getPosition(),
        ];
    }

    /**
     * Get the position details.
     *
     * @return array|null
     */
    private function getPosition(): ?array
    {
        if (!$this->relationLoaded('position') || !$this->position) {
            return null;
        }

        return [
            'id'            => $this->position->id ?? null,
            'department_id' => $this->position->department_id ?? null,
            'department'    => $this->getDepartment(),
            'designation_id' => $this->position->designation_id ?? null,
            'designation'   => $this->getDesignation(),
            'section_head'  => $this->position->section_head ?? null,
        ];
    }

    /**
     * Get the department details.
     *
     * @return array|null
     */
    private function getDepartment(): ?array
    {
        if (!$this->position->relationLoaded('department') || !$this->position->department) {
            return null;
        }

        return [
            'id'   => $this->position->department->id ?? null,
            'name' => $this->position->department->name ?? null,
        ];
    }

    /**
     * Get the designation details.
     *
     * @return array|null
     */
    private function getDesignation(): ?array
    {
        if (!$this->position->relationLoaded('designation') || !$this->position->designation) {
            return null;
        }

        return [
            'id'   => $this->position->designation->id ?? null,
            'designation' => $this->position->designation->designation ?? null,
        ];
    }
}
