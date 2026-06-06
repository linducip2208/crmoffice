<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Web-to-lead public webhook.
 *
 * Admin generates a form token per lead source (e.g. "contact-form-site",
 * "demo-request"). The token identifies which source to attribute leads to.
 */
class LeadWebhookController extends Controller
{
    public function handle(Request $request, string $token): JsonResponse
    {
        $source = LeadSource::query()
            ->where('form_token', $token)
            ->where('is_active', true)
            ->first();

        if (! $source) {
            return response()->json(['error' => 'Unknown form'], 404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:180',
            'email' => 'required|email',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:40',
            'message' => 'nullable|string|max:2000',
            'website' => 'nullable|url',
            // honeypot
            'website_url' => 'nullable|prohibited',
        ]);

        $defaultStatus = LeadStatus::where('is_default', true)->orderBy('sort_order')->first()
            ?? LeadStatus::orderBy('sort_order')->firstOrFail();

        $lead = Lead::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'company' => $data['company'] ?? null,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'description' => $data['message'] ?? null,
            'lead_source_id' => $source->id,
            'lead_status_id' => $defaultStatus->id,
            'last_activity_at' => now(),
        ]);

        return response()->json(['ok' => true, 'lead_id' => $lead->id], 201);
    }
}
