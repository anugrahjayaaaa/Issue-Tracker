<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeatureResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug' => $this->resource['slug'],
            'label' => $this->resource['label'],
            'enabled' => (bool) $this->resource['enabled'],
        ];
    }
}
