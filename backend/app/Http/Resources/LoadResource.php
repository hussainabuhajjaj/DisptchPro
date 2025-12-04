<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoadResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'load_number' => $this->load_number,
            'client' => new ClientResource($this->whenLoaded('client')),
            'carrier' => new CarrierResource($this->whenLoaded('carrier')),
            'driver' => new DriverResource($this->whenLoaded('driver')),
            'truck_id' => $this->truck_id,
            'trailer_id' => $this->trailer_id,
            'status' => $this->status,
            'rate_to_client' => $this->rate_to_client,
            'rate_to_carrier' => $this->rate_to_carrier,
            'fuel_surcharge' => $this->fuel_surcharge,
            'distance_miles' => $this->distance_miles,
            'commodity' => $this->commodity,
            'weight' => $this->weight,
            'pieces' => $this->pieces,
            'reference_numbers' => $this->reference_numbers,
            'stops' => LoadStopResource::collection($this->whenLoaded('stops')),
            'profit' => $this->profit,
            'margin' => $this->margin,
            'created_at' => $this->created_at,
        ];
    }
}
