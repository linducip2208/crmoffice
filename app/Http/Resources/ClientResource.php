<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'industry' => $this->industry,
            'website' => $this->website,
            'phone' => $this->phone,
            'billing_address' => $this->billing_address,
            'billing_city' => $this->billing_city,
            'billing_state' => $this->billing_state,
            'billing_country' => $this->billing_country,
            'billing_postal' => $this->billing_postal,
            'shipping_address' => $this->shipping_address,
            'shipping_city' => $this->shipping_city,
            'shipping_state' => $this->shipping_state,
            'shipping_country' => $this->shipping_country,
            'shipping_postal' => $this->shipping_postal,
            'tax_id' => $this->tax_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'contacts' => ContactResource::collection($this->whenLoaded('contacts')),
            'account_manager' => new UserResource($this->whenLoaded('accountManager')),
            'primary_contact' => new ContactResource($this->whenLoaded('primaryContact')),
            'projects_count' => $this->when($this->projects_count !== null, $this->projects_count),
            'invoices_count' => $this->when($this->invoices_count !== null, $this->invoices_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
