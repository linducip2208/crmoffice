<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalProjectController extends Controller
{
    public function index(): View
    {
        $contact = Auth::guard('portal')->user();
        $client = $contact->client;
        $projects = $client->projects()
            ->where('is_visible_to_customer', true)
            ->latest()
            ->paginate(15);

        return view('portal.projects.index', [
            'client' => $client,
            'projects' => $projects,
        ]);
    }

    public function show($id): View
    {
        $contact = Auth::guard('portal')->user();
        $client = $contact->client;
        $project = $client->projects()
            ->where('is_visible_to_customer', true)
            ->with(['milestones', 'tasks' => fn ($q) => $q->where('is_visible_to_customer', true)])
            ->findOrFail($id);

        return view('portal.projects.show', [
            'client' => $client,
            'project' => $project,
        ]);
    }
}
