<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:180',
            'source' => 'nullable|string|max:60',
            // honeypot
            'website' => 'nullable|prohibited',
        ]);

        $sub = NewsletterSubscriber::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'] ?? null,
                'source' => $data['source'] ?? request()->headers->get('referer'),
                'is_active' => true,
            ],
        );

        return response()->json([
            'ok' => true,
            'already_subscribed' => ! $sub->wasRecentlyCreated,
        ]);
    }
}
