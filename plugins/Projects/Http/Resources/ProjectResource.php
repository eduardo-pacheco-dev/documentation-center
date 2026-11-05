<?php

namespace Plugins\Projects\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Plugins\Projects\Enums\ProjectStatus;

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
            'name' => $this->name,
            'status' => $this->status instanceof ProjectStatus ? $this->status->label() : $this->status,
        ];
    }
}
