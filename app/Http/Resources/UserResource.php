<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'role' => $this->role,
            'image' => $this->image,
            'position' => $this->whenLoaded('position', function () {
                return [
                    'department_id' => $this->position->department_id,
                    'department' => $this->position->department->name,
                    'designation_id' => $this->position->designation_id,
                    'designation' => $this->position->designation->designation,
                ];
            }),
        ];
    }
}
