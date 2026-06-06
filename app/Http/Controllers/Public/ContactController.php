<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('marketing.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:180',
            'company' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:40',
            'plan' => 'nullable|in:self-host,growth,whitelabel,other',
            'message' => 'required|string|min:10|max:5000',
        ]);

        $source = LeadSource::firstOrCreate(
            ['name' => 'Website Contact Form'],
            ['is_active' => true]
        );

        $newStatus = LeadStatus::orderBy('order')->first();

        if (! $newStatus) {
            return back()
                ->withInput()
                ->with('contact_error', 'Konfigurasi server belum siap (lead status kosong). Hubungi admin.');
        }

        $planLine = $data['plan'] ? "\n\nMinat paket: " . $data['plan'] : '';
        $description = $data['message'] . $planLine;

        Lead::create([
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'description' => $description,
            'lead_source_id' => $source->id,
            'lead_status_id' => $newStatus->id,
            'last_activity_at' => now(),
        ]);

        return back()->with('contact_success', 'Terima kasih! Tim kami akan hubungi kamu dalam 1×24 jam.');
    }
}
