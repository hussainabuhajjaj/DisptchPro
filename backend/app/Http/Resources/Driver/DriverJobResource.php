<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverJobResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'load_number' => $this->load_number,
            'status' => $this->status,
            'route_status' => $this->route_status,
            'last_lat' => $this->last_lat,
            'last_lng' => $this->last_lng,
            'last_location_at' => optional($this->last_location_at)?->toIso8601String(),
            'last_eta_minutes' => $this->last_eta_minutes,
            'stops' => DriverStopResource::collection($this->whenLoaded('stops')),
        ];
    }
}
