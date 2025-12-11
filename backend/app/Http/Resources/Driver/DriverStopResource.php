<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverStopResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'city' => $this->city,
            'state' => $this->state,
            'date_from' => optional($this->date_from)?->toIso8601String(),
            'appointment_time' => $this->appointment_time,
            'lat' => $this->lat,
            'lng' => $this->lng,
        ];
    }
}
