<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if ($query === '') {
            return response()->json(['data' => (object) []]);
        }

        $types = $request->query('types')
            ? explode(',', $request->query('types'))
            : ['clients', 'leads', 'invoices', 'projects', 'tasks', 'tickets'];

        $limit = min((int) $request->query('limit', 8), 25);
        $results = [];

        $map = [
            'clients' => fn () => Client::search($query)->take($limit)->get()->map(fn ($c) => ['id' => $c->id, 'title' => $c->company_name, 'subtitle' => $c->industry, 'url' => "/admin/clients/{$c->id}/edit"]),
            'leads' => fn () => Lead::search($query)->take($limit)->get()->map(fn ($l) => ['id' => $l->id, 'title' => $l->name, 'subtitle' => $l->company ?: $l->email, 'url' => "/admin/leads/{$l->id}/edit"]),
            'invoices' => fn () => Invoice::search($query)->take($limit)->get()->map(fn ($i) => ['id' => $i->id, 'title' => $i->number, 'subtitle' => $i->status, 'url' => "/admin/invoices/{$i->id}/edit"]),
            'projects' => fn () => Project::search($query)->take($limit)->get()->map(fn ($p) => ['id' => $p->id, 'title' => $p->name, 'subtitle' => $p->status, 'url' => "/admin/projects/{$p->id}/edit"]),
            'tasks' => fn () => Task::search($query)->take($limit)->get()->map(fn ($t) => ['id' => $t->id, 'title' => $t->title, 'subtitle' => $t->status, 'url' => "/admin/tasks/{$t->id}/edit"]),
            'tickets' => fn () => Ticket::search($query)->take($limit)->get()->map(fn ($t) => ['id' => $t->id, 'title' => "{$t->number} {$t->subject}", 'subtitle' => null, 'url' => "/admin/tickets/{$t->id}/edit"]),
        ];

        foreach ($types as $type) {
            if (isset($map[$type])) {
                $results[$type] = $map[$type]();
            }
        }

        return response()->json(['data' => $results]);
    }
}
