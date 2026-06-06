<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class LeadIntakeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Rate limit per IP — 10 leads / hour from same IP
        $key = 'lead-intake:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'error' => ['code' => 'RATE_LIMITED', 'message' => 'Too many submissions, try again later.'],
            ], 429);
        }
        RateLimiter::hit($key, 3600);

        $data = $request->validate([
            'name' => 'required|string|max:180',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:40',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:5000',
            'source' => 'nullable|string|max:120',
        ]);

        $sourceName = $data['source'] ?? 'Website';
        $source = LeadSource::firstOrCreate(['name' => $sourceName], ['is_active' => true]);
        $newStatus = LeadStatus::orderBy('order')->first();

        if (! $newStatus) {
            return response()->json([
                'error' => ['code' => 'CONFIG_MISSING', 'message' => 'No lead statuses configured.'],
            ], 500);
        }

        $lead = Lead::create([
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'description' => $data['description'] ?? null,
            'lead_source_id' => $source->id,
            'lead_status_id' => $newStatus->id,
            'last_activity_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $lead->id,
                'message' => 'Lead submitted successfully.',
            ],
        ], 201);
    }
}
