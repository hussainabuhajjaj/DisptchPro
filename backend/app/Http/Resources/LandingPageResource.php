<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LandingPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'sections' => LandingSectionResource::collection($this['sections']),
            'settings' => $this['settings'],
            'media' => $this['media'] ? new MediaResource($this['media']) : null,
        ];
    }
}
