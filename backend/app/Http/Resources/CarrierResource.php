<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CarrierResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'MC_number' => $this->MC_number,
            'DOT_number' => $this->DOT_number,
            'phone' => $this->phone,
            'email' => $this->email,
            'onboarding_status' => $this->onboarding_status,
        ];
    }
}
