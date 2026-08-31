<?php

namespace App\Http\Resources;

use App\Models\Issue;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'type_name' => $this->typeName(),
            'status' => $this->status,
            'status_name' => $this->statusName(),
            'priority' => $this->priority,
            'reporter_id' => $this->reporter_id,
            'assignee_id' => $this->assignee_id,
            'parent_id' => $this->parent_id,
            'due_date' => $this->due_date?->toDateString(),
            'order' => $this->order,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            'reporter' => new UserResource($this->whenLoaded('reporter')),
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'parent' => IssueResource::make($this->whenLoaded('parent')),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'watchers' => UserResource::collection($this->whenLoaded('watchers')),
            'comments_count' => $this->comments()->count(),
            'attachments_count' => $this->attachments()->count(),
            'subtask_progress' => $this->subtaskProgress(),
        ];
    }
}
