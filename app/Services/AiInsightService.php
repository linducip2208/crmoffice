<?php

namespace App\Services;

use App\Adapters\Llm\LlmAdapterFactory;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiInsightService
{
    public function query(string $question, User $user): array
    {
        $lower = mb_strtolower(trim($question));

        $handler = $this->detectHandler($lower);

        if ($handler) {
            try {
                return $handler($user);
            } catch (\Throwable $e) {
                Log::warning('AiInsightService structured query failed: ' . $e->getMessage());
            }
        }

        return $this->llmFallback($question, $user);
    }

    private function detectHandler(string $question): ?callable
    {
        $patterns = [
            '/client\s*(paling|ter|paling\s*)?profit|profit\s*per\s*client|client\s*(pemasukan|revenue)\s*terbesar/i'
                => 'topClientsByRevenue',

            '/task\s*(overdue|terlambat|lewat|belum\s*selesai)|tugas\s*(overdue|terlambat|lewat|belum)/i'
                => 'overdueTasks',

            '/revenue\s*(bulan\s*ini|month|bulanan)|pendapatan\s*(bulan\s*ini|month)|pemasukan\s*(bulan\s*ini|month)/i'
                => 'revenueThisMonth',

            '/lead\s*conversion|konversi\s*lead|lead\s*(rate|ratio)/i'
                => 'leadConversionRate',

            '/ticket\s*(open|terbuka|aktif|belum\s*selesai)/i'
                => 'openTickets',

            '/project\s*(progress|progres|kemajuan|status)/i'
                => 'projectProgress',

            '/top\s*sales|sales\s*terbaik|sales\s*(person|orang)\s*terbaik|deals?\s*terbanyak/i'
                => 'topSalesPeople',

            '/invoice\s*(overdue|terlambat|belum\s*dibayar|jatuh\s*tempo)/i'
                => 'overdueInvoices',

            '/cashflow|arus\s*kas/i'
                => 'cashflowPrediction',

            '/task\s*saya|tugas\s*saya|my\s*tasks?/i'
                => fn (User $u) => $this->myTasks($u),
        ];

        foreach ($patterns as $regex => $handler) {
            if (preg_match($regex, $question)) {
                return is_string($handler) ? [$this, $handler] : $handler;
            }
        }

        return null;
    }

    private function topClientsByRevenue(): array
    {
        $clients = Client::select('clients.id', 'clients.company_name')
            ->join('invoices', 'clients.id', '=', 'invoices.client_id')
            ->join('payments', 'invoices.id', '=', 'payments.invoice_id')
            ->where('payments.status', 'completed')
            ->groupBy('clients.id', 'clients.company_name')
            ->selectRaw('SUM(payments.amount) as total_revenue')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        if ($clients->isEmpty()) {
            return [
                'answer' => 'Belum ada data pembayaran yang tercatat.',
                'data' => null,
                'chart_type' => null,
            ];
        }

        return [
            'answer' => $this->formatTopClients($clients),
            'data' => [
                'labels' => $clients->pluck('company_name')->toArray(),
                'values' => $clients->pluck('total_revenue')->map(fn ($v) => (float) $v)->toArray(),
            ],
            'chart_type' => 'bar',
        ];
    }

    private function overdueTasks(): array
    {
        $tasks = Task::where('due_date', '<', now()->startOfDay())
            ->whereNotIn('status', ['done', 'completed', 'cancelled'])
            ->with('project:id,name', 'assignees:id,name')
            ->orderBy('due_date')
            ->limit(20)
            ->get();

        if ($tasks->isEmpty()) {
            return [
                'answer' => 'Tidak ada task yang overdue. Semua task tepat waktu.',
                'data' => null,
                'chart_type' => null,
            ];
        }

        $count = $tasks->count();
        $highPriority = $tasks->where('priority', 'high')->count();
        $lines = ["Ditemukan **{$count} task** yang overdue:"];
        foreach ($tasks as $t) {
            $project = $t->project?->name ?? 'No Project';
            $assignee = $t->assignees->pluck('name')->implode(', ') ?: 'Unassigned';
            $lines[] = "- **{$t->title}** — Due: {$t->due_date->format('d M Y')} — {$project} — {$assignee}";
        }
        if ($highPriority > 0) {
            $lines[] = "\n{$highPriority} task berprioritas tinggi. Segera tindak lanjuti.";
        }

        return [
            'answer' => implode("\n", $lines),
            'data' => [
                'labels' => $tasks->pluck('title')->map(fn ($t) => mb_strimwidth($t, 0, 30, '...'))->toArray(),
                'values' => $tasks->map(fn ($t) => now()->startOfDay()->diffInDays($t->due_date, false))->map(fn ($v) => abs((int) $v))->toArray(),
            ],
            'chart_type' => 'bar',
        ];
    }

    private function revenueThisMonth(): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $total = Payment::where('status', 'completed')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount');

        $currency = \App\Models\Setting::get('default_currency', 'IDR');

        return [
            'answer' => "Revenue bulan ini: **" . number_format((float) $total, 2) . " {$currency}**.",
            'data' => [
                'value' => (float) $total,
                'currency' => $currency,
            ],
            'chart_type' => null,
        ];
    }

    private function leadConversionRate(): array
    {
        $totalLeads = Lead::count();
        $wonLeads = Lead::whereHas('status', fn ($q) => $q->where('is_won', true))->count();
        $lostLeads = Lead::whereHas('status', fn ($q) => $q->where('is_lost', true))->count();

        if ($totalLeads === 0) {
            return [
                'answer' => 'Belum ada data lead yang tercatat.',
                'data' => null,
                'chart_type' => null,
            ];
        }

        $rate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0;

        return [
            'answer' => "Lead conversion rate: **{$rate}%**\n- Total leads: {$totalLeads}\n- Won: {$wonLeads}\n- Lost: {$lostLeads}\n- Open/In Progress: " . ($totalLeads - $wonLeads - $lostLeads),
            'data' => [
                'labels' => ['Won', 'Lost', 'Open'],
                'values' => [$wonLeads, $lostLeads, $totalLeads - $wonLeads - $lostLeads],
            ],
            'chart_type' => 'doughnut',
        ];
    }

    private function openTickets(): array
    {
        $tickets = Ticket::whereHas('status', fn ($q) => $q->whereNotIn('name', ['Resolved', 'Closed', 'Cancelled']))
            ->with('priority:id,name', 'assignee:id,name', 'department:id,name')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        if ($tickets->isEmpty()) {
            return [
                'answer' => 'Semua ticket sudah terselesaikan. Tidak ada ticket yang terbuka.',
                'data' => null,
                'chart_type' => null,
            ];
        }

        $byPriority = $tickets->groupBy(fn ($t) => $t->priority?->name ?? 'Unknown');
        $lines = ["**{$tickets->count()} ticket** masih terbuka:"];
        foreach ($byPriority as $priority => $group) {
            $lines[] = "- {$priority}: {$group->count()} ticket";
        }
        $lines[] = '';
        foreach ($tickets->take(5) as $t) {
            $lines[] = "- #{$t->number} {$t->subject} (" . ($t->priority?->name ?? '-') . ')';
        }

        return [
            'answer' => implode("\n", $lines),
            'data' => [
                'labels' => $byPriority->keys()->toArray(),
                'values' => $byPriority->map(fn ($g) => $g->count())->values()->toArray(),
            ],
            'chart_type' => 'doughnut',
        ];
    }

    private function projectProgress(): array
    {
        $projects = Project::orderBy('progress_pct', 'desc')
            ->with('projectManager:id,name', 'client:id,company_name')
            ->limit(10)
            ->get();

        if ($projects->isEmpty()) {
            return [
                'answer' => 'Belum ada project yang tercatat.',
                'data' => null,
                'chart_type' => null,
            ];
        }

        $lines = ["Progress project:"];
        foreach ($projects as $p) {
            $pm = $p->projectManager?->name ?? '-';
            $lines[] = "- **{$p->name}** — {$p->progress_pct}% — PM: {$pm}";
        }

        return [
            'answer' => implode("\n", $lines),
            'data' => [
                'labels' => $projects->pluck('name')->toArray(),
                'values' => $projects->pluck('progress_pct')->map(fn ($v) => (float) $v)->toArray(),
            ],
            'chart_type' => 'bar',
        ];
    }

    private function topSalesPeople(): array
    {
        $users = User::whereHas('assignedLeads.status', fn ($q) => $q->where('is_won', true))
            ->withCount(['assignedLeads as deals_won' => fn ($q) => $q->whereHas('status', fn ($sq) => $sq->where('is_won', true))])
            ->withCount(['assignedLeads as total_leads' => fn ($q) => $q->whereHas('status')])
            ->select('users.id', 'users.name')
            ->orderByDesc('deals_won')
            ->limit(10)
            ->get();

        if ($users->isEmpty()) {
            return [
                'answer' => 'Belum ada data deal yang tercatat.',
                'data' => null,
                'chart_type' => null,
            ];
        }

        $lines = ["Top sales berdasarkan deals closed:"];
        foreach ($users as $u) {
            $rate = $u->total_leads > 0 ? round(($u->deals_won / $u->total_leads) * 100) : 0;
            $lines[] = "- **{$u->name}** — {$u->deals_won} deals won ({$rate}%)";
        }

        return [
            'answer' => implode("\n", $lines),
            'data' => [
                'labels' => $users->pluck('name')->toArray(),
                'values' => $users->pluck('deals_won')->map(fn ($v) => (int) $v)->toArray(),
            ],
            'chart_type' => 'bar',
        ];
    }

    private function overdueInvoices(): array
    {
        $invoices = Invoice::where('due_date', '<', now()->startOfDay())
            ->where('balance_due', '>', 0)
            ->whereNotIn('status', ['paid', 'cancelled', 'void'])
            ->with('client:id,company_name')
            ->orderBy('due_date')
            ->limit(15)
            ->get();

        if ($invoices->isEmpty()) {
            return [
                'answer' => 'Tidak ada invoice yang overdue. Semua invoice sudah dibayar.',
                'data' => null,
                'chart_type' => null,
            ];
        }

        $totalDue = $invoices->sum('balance_due');
        $currency = \App\Models\Setting::get('default_currency', 'IDR');
        $lines = ["**{$invoices->count()} invoice** overdue — total: **" . number_format((float) $totalDue, 2) . " {$currency}**:"];
        foreach ($invoices->take(10) as $inv) {
            $client = $inv->client?->company_name ?? 'Unknown';
            $lines[] = "- INV-{$inv->number} — {$client} — " . number_format((float) $inv->balance_due, 2) . " {$currency} — Due: {$inv->due_date->format('d M Y')}";
        }

        return [
            'answer' => implode("\n", $lines),
            'data' => [
                'labels' => $invoices->pluck('number')->toArray(),
                'values' => $invoices->pluck('balance_due')->map(fn ($v) => (float) $v)->toArray(),
            ],
            'chart_type' => 'bar',
        ];
    }

    private function cashflowPrediction(): array
    {
        $thisMonthRevenue = Payment::where('status', 'completed')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $overdueInvoices = Invoice::where('due_date', '<', now()->startOfDay())
            ->where('balance_due', '>', 0)
            ->whereNotIn('status', ['paid', 'cancelled', 'void'])
            ->sum('balance_due');

        $upcomingInvoices = Invoice::where('due_date', '>=', now()->startOfDay())
            ->where('due_date', '<=', now()->addDays(30)->endOfDay())
            ->where('balance_due', '>', 0)
            ->whereNotIn('status', ['paid', 'cancelled', 'void'])
            ->sum('total');

        $currency = \App\Models\Setting::get('default_currency', 'IDR');

        $lines = ["Prediksi cashflow:"];
        $lines[] = "- Revenue bulan ini: **" . number_format((float) $thisMonthRevenue, 2) . " {$currency}**";
        $lines[] = "- Piutang overdue: **" . number_format((float) $overdueInvoices, 2) . " {$currency}**";
        $lines[] = "- Invoice jatuh tempo (30 hari): **" . number_format((float) $upcomingInvoices, 2) . " {$currency}**";
        $netCash = $thisMonthRevenue - $overdueInvoices;
        $lines[] = "- Net cash position: **" . number_format((float) $netCash, 2) . " {$currency}**";

        return [
            'answer' => implode("\n", $lines),
            'data' => [
                'revenue_this_month' => (float) $thisMonthRevenue,
                'overdue' => (float) $overdueInvoices,
                'upcoming_30d' => (float) $upcomingInvoices,
                'net_cash' => (float) $netCash,
            ],
            'chart_type' => null,
        ];
    }

    private function myTasks(User $user): array
    {
        $tasks = Task::whereHas('assignees', fn ($q) => $q->where('user_id', $user->id))
            ->whereNotIn('status', ['done', 'completed', 'cancelled'])
            ->with('project:id,name')
            ->orderBy('due_date')
            ->limit(15)
            ->get();

        if ($tasks->isEmpty()) {
            return [
                'answer' => 'Kamu tidak punya task yang pending. Semua task sudah selesai.',
                'data' => null,
                'chart_type' => null,
            ];
        }

        $overdue = $tasks->filter(fn ($t) => $t->due_date && $t->due_date->lt(now()->startOfDay()));
        $lines = ["Task kamu: **{$tasks->count()} pending**" . ($overdue->isNotEmpty() ? " ({$overdue->count()} overdue)" : "") . ":"];
        foreach ($tasks as $t) {
            $due = $t->due_date ? $t->due_date->format('d M Y') : 'No date';
            $prefix = $t->due_date && $t->due_date->lt(now()->startOfDay()) ? 'OVERDUE ' : '';
            $projectName = $t->project?->name ?? 'No Project';
            $lines[] = "- {$prefix}**{$t->title}** — Due: {$due} — {$projectName}";
        }

        return [
            'answer' => implode("\n", $lines),
            'data' => null,
            'chart_type' => null,
        ];
    }

    private function llmFallback(string $question, User $user): array
    {
        try {
            $adapter = LlmAdapterFactory::active();
            if (!$adapter) {
                return ['answer' => 'AI tidak tersedia. Silakan konfigurasi LLM provider di Settings → Providers.', 'data' => null, 'chart_type' => null];
            }

            $context = $this->buildContext($user);
            $messages = [
                ['role' => 'system', 'content' => "Kamu adalah asisten CRM yang membantu menjawab pertanyaan bisnis. Jawab dalam Bahasa Indonesia, ringkas dan informatif. Berikut konteks data saat ini:\n\n{$context}"],
                ['role' => 'user', 'content' => $question],
            ];

            $response = $adapter->chat($messages, ['temperature' => 0.5, 'max_tokens' => 600]);

            return [
                'answer' => $response->content ?? 'Maaf, tidak dapat menjawab pertanyaan saat ini.',
                'data' => null,
                'chart_type' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('AiInsightService LLM fallback failed: ' . $e->getMessage());
            return [
                'answer' => 'Maaf, terjadi kesalahan saat memproses pertanyaan. Coba lagi nanti.',
                'data' => null,
                'chart_type' => null,
            ];
        }
    }

    private function buildContext(User $user): string
    {
        $leadsTotal = Lead::count();
        $leadsWon = Lead::whereHas('status', fn ($q) => $q->where('is_won', true))->count();
        $invoicesOverdue = Invoice::where('due_date', '<', now())->where('balance_due', '>', 0)->whereNotIn('status', ['paid', 'cancelled'])->count();
        $tasksOverdue = Task::where('due_date', '<', now())->whereNotIn('status', ['done', 'completed'])->count();
        $ticketsOpen = Ticket::whereHas('status', fn ($q) => $q->whereNotIn('name', ['Resolved', 'Closed']))->count();
        $projectsActive = Project::whereNotIn('status', ['completed', 'cancelled'])->count();

        return "Ringkasan data CRM saat ini:\n"
            . "- Total leads: {$leadsTotal} (won: {$leadsWon})\n"
            . "- Invoice overdue: {$invoicesOverdue}\n"
            . "- Task overdue: {$tasksOverdue}\n"
            . "- Ticket terbuka: {$ticketsOpen}\n"
            . "- Project aktif: {$projectsActive}\n"
            . "- User saat ini: {$user->name}";
    }

    private function formatTopClients($clients): string
    {
        $currency = \App\Models\Setting::get('default_currency', 'IDR');
        $lines = ["Klien paling profitable:"];
        foreach ($clients as $c) {
            $lines[] = "- **{$c->company_name}** — " . number_format((float) $c->total_revenue, 2) . " {$currency}";
        }
        return implode("\n", $lines);
    }
}
