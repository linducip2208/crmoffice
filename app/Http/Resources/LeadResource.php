<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company' => $this->company,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'estimated_value' => (float) $this->estimated_value,
            'description' => $this->description,
            'expected_close' => $this->expected_close,
            'converted_at' => $this->converted_at,
            'last_activity_at' => $this->last_activity_at,
            'source' => $this->whenLoaded('source'),
            'status' => $this->whenLoaded('status'),
            'assigned_to' => new UserResource($this->whenLoaded('assignedTo')),
            'converted_client' => new ClientResource($this->whenLoaded('convertedClient')),
            'proposals' => $this->whenLoaded('proposals'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
