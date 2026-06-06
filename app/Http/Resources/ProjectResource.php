<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'client_id' => $this->client_id,
            'project_manager_id' => $this->project_manager_id,
            'start_date' => $this->start_date,
            'deadline' => $this->deadline,
            'estimate_hours' => (float) $this->estimate_hours,
            'billing_method' => $this->billing_method,
            'fixed_price' => (float) $this->fixed_price,
            'hourly_rate' => (float) $this->hourly_rate,
            'status' => $this->status,
            'progress_pct' => (float) $this->progress_pct,
            'is_visible_to_customer' => $this->is_visible_to_customer,
            'client' => new ClientResource($this->whenLoaded('client')),
            'project_manager' => new UserResource($this->whenLoaded('projectManager')),
            'members' => UserResource::collection($this->whenLoaded('members')),
            'milestones' => $this->whenLoaded('milestones'),
            'tasks_count' => $this->when($this->tasks_count !== null, $this->tasks_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
