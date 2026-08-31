<?php

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => $this->name,
            'description' => $this->description,
            'owner_id' => $this->owner_id,
            'next_issue_code' => $this->next_issue_code,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'members_count' => $this->members()->count(),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
        ];
    }
}
