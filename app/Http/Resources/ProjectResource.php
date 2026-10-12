<?php

namespace App\Http\Resources;

use App\Enums\ProjectStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Get the resource's attributes for the given request.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->code ?? (string) $this->getKey(),
            'siteId' => $this->erb?->code,
            'name' => $this->name,
            'status' => $this->status instanceof ProjectStatus ? $this->status->label() : $this->status,
        ];
    }
}
