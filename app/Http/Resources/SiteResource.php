<?php

namespace App\Http\Resources;

use App\Enums\ErbStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteResource extends JsonResource
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
            'assignedTo' => $this->assigned_to,
            'siteId' => $this->site_id,
            'endId' => $this->end_id,
            'name' => $this->name,
            'city' => $this->city,
            'state' => $this->state,
            'status' => $this->status instanceof ErbStatus ? $this->status->label() : $this->status,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'nokiaSmpId' => $this->nokia_smp_id,
            'startDate' => $this->start_date?->toDateString(),
            'endDate' => $this->end_date?->toDateString(),
            'serviceOrderId' => $this->service_order_id,
        ];
    }
}
