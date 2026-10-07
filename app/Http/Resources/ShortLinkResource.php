<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShortLinkResource extends JsonResource
{
    /**
     * Get the resource's attributes for the given request.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'url' => $this->url,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'expires_at' => $this->whenNotNull($this->expires_at?->toIso8601String()),
            'max_uses' => $this->max_uses,
            'used_count' => $this->used_count,
            'has_password' => $this->needsPassword(),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
            'documents_count' => $this->whenCounted('documents'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
