<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{
    /**
     * Get the resource's attributes for the given request.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->code,
            'projectId' => $this->project?->code,
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'createdAt' => $this->created_at?->toDateString(),
            'fields' => $this->fields,
            'steps' => $this->steps ?? [],
        ];
    }
}
