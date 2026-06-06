<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'subject' => $this->subject,
            'body' => $this->body,
            'client_id' => $this->client_id,
            'contact_id' => $this->contact_id,
            'email_from' => $this->email_from,
            'department_id' => $this->department_id,
            'priority_id' => $this->priority_id,
            'status_id' => $this->status_id,
            'assigned_to' => $this->assigned_to,
            'related_project_id' => $this->related_project_id,
            'first_response_at' => $this->first_response_at,
            'first_response_due_at' => $this->first_response_due_at,
            'resolved_at' => $this->resolved_at,
            'resolve_due_at' => $this->resolve_due_at,
            'closed_at' => $this->closed_at,
            'client' => new ClientResource($this->whenLoaded('client')),
            'contact' => new ContactResource($this->whenLoaded('contact')),
            'department' => $this->whenLoaded('department'),
            'priority' => $this->whenLoaded('priority'),
            'status' => $this->whenLoaded('status'),
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'replies' => $this->whenLoaded('replies'),
            'replies_count' => $this->when($this->replies_count !== null, $this->replies_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
