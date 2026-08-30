<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->description,
            'causer' => $this->causer?->username,
            'subject_type' => $this->subject_type ? class_basename($this->subject_type) : null,
            'subject_id' => $this->subject_id,
            'ip' => $this->properties['ip'] ?? null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
