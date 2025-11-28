<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'hero_image_url' => $this->formatUrl($this->hero_image_url),
            'why_choose_us_image_url' => $this->formatUrl($this->why_choose_us_image_url),
            'for_shippers_image_url' => $this->formatUrl($this->for_shippers_image_url),
            'for_brokers_image_url' => $this->formatUrl($this->for_brokers_image_url),
            'testimonial_avatar_1_url' => $this->formatUrl($this->testimonial_avatar_1_url),
            'testimonial_avatar_2_url' => $this->formatUrl($this->testimonial_avatar_2_url),
            'testimonial_avatar_3_url' => $this->formatUrl($this->testimonial_avatar_3_url),
            'meta' => [
                'hero_image_meta' => $this->hero_image_meta,
                'why_choose_us_image_meta' => $this->why_choose_us_image_meta,
                'for_shippers_image_meta' => $this->for_shippers_image_meta,
                'for_brokers_image_meta' => $this->for_brokers_image_meta,
                'testimonial_avatar_1_meta' => $this->testimonial_avatar_1_meta,
                'testimonial_avatar_2_meta' => $this->testimonial_avatar_2_meta,
                'testimonial_avatar_3_meta' => $this->testimonial_avatar_3_meta,
            ],
        ];
    }

    protected function formatUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
