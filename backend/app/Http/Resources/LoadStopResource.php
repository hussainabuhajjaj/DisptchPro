<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoadStopResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sequence' => $this->sequence,
            'type' => $this->type,
            'facility_name' => $this->facility_name,
            'city' => $this->city,
            'state' => $this->state,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
        ];
    }
}
