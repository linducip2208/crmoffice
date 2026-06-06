<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'milestone_id' => $this->milestone_id,
            'parent_task_id' => $this->parent_task_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'due_date' => $this->due_date,
            'estimate_hours' => (float) $this->estimate_hours,
            'is_billable' => $this->is_billable,
            'hourly_rate' => (float) $this->hourly_rate,
            'is_visible_to_customer' => $this->is_visible_to_customer,
            'order' => $this->order,
            'completed_at' => $this->completed_at,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'milestone' => $this->whenLoaded('milestone'),
            'parent' => new self($this->whenLoaded('parent')),
            'subtasks' => self::collection($this->whenLoaded('subtasks')),
            'assignees' => UserResource::collection($this->whenLoaded('assignees')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
