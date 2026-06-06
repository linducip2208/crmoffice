<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Client;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use Filament\Widgets\Widget;

class KpiOverviewWidget extends Widget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 7;
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.admin.widgets.kpi-overview';

    protected function getViewData(): array
    {
        return [
            'revenue' => $this->revenueKpis(),
            'crm' => $this->crmKpis(),
            'projects' => $this->projectKpis(),
            'support' => $this->supportKpis(),
        ];
    }

    protected function revenueKpis(): array
    {
        $now = now();
        $thisMonth = (int) Invoice::query()
            ->where('status', 'paid')
            ->whereMonth('invoice_date', $now->month)
            ->whereYear('invoice_date', $now->year)
            ->sum('total');

        $lastMonth = (int) Invoice::query()
            ->where('status', 'paid')
            ->whereMonth('invoice_date', $now->copy()->subMonth()->month)
            ->whereYear('invoice_date', $now->copy()->subMonth()->year)
            ->sum('total');

        $goal = Goal::query()
            ->where('metric', 'revenue')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->first();
        $target = $goal ? (float) $goal->target : null;

        $outstanding = (int) Invoice::query()
            ->whereNotIn('status', ['paid', 'void', 'draft', 'cancelled'])
            ->sum('balance_due');

        $lastMonthOutstanding = (int) Invoice::query()
            ->whereNotIn('status', ['paid', 'void', 'draft', 'cancelled'])
            ->whereMonth('invoice_date', $now->copy()->subMonth()->month)
            ->whereYear('invoice_date', $now->copy()->subMonth()->year)
            ->sum('balance_due');

        $paidTotal = (int) Invoice::query()
            ->where('status', 'paid')
            ->sum('total');
        $collectionRate = ($paidTotal + $outstanding) > 0
            ? round(($paidTotal / ($paidTotal + $outstanding)) * 100, 1)
            : 0;

        $lastPaidTotal = (int) Invoice::query()
            ->where('status', 'paid')
            ->where('invoice_date', '<', $now->startOfMonth())
            ->sum('total');
        $lastOutstanding = (int) Invoice::query()
            ->whereNotIn('status', ['paid', 'void', 'draft', 'cancelled'])
            ->where('invoice_date', '<', $now->startOfMonth())
            ->sum('balance_due');
        $lastCollectionRate = ($lastPaidTotal + $lastOutstanding) > 0
            ? round(($lastPaidTotal / ($lastPaidTotal + $lastOutstanding)) * 100, 1)
            : 0;

        return [
            [
                'label' => 'MTD Revenue',
                'value' => number_format($thisMonth, 0),
                'trend' => $this->trend($thisMonth, $lastMonth),
                'icon' => '💵',
            ],
            [
                'label' => 'MTD Target',
                'value' => $target !== null ? number_format($target, 0) : '—',
                'trend' => $target !== null && $target > 0
                    ? ['pct' => round(($thisMonth / $target) * 100, 1), 'up' => true]
                    : null,
                'icon' => '🎯',
            ],
            [
                'label' => 'Outstanding AR',
                'value' => number_format($outstanding, 0),
                'trend' => $this->trend($outstanding, $lastMonthOutstanding, true),
                'icon' => '📊',
            ],
            [
                'label' => 'Collection Rate',
                'value' => $collectionRate . '%',
                'trend' => ['pct' => round($collectionRate - $lastCollectionRate, 1), 'up' => $collectionRate >= $lastCollectionRate],
                'icon' => '✅',
            ],
        ];
    }

    protected function crmKpis(): array
    {
        $now = now();

        $totalClients = Client::query()->count();
        $lastMonthClients = Client::query()
            ->where('created_at', '<', $now->startOfMonth())
            ->count();

        $newLeads = Lead::query()
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();
        $lastMonthLeads = Lead::query()
            ->whereMonth('created_at', $now->copy()->subMonth()->month)
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->count();

        $totalLeads = Lead::query()->count();
        $convertedLeads = Lead::query()->whereNotNull('converted_at')->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        $lastTotalLeads = Lead::query()
            ->where('created_at', '<', $now->startOfMonth())
            ->count();
        $lastConverted = Lead::query()
            ->whereNotNull('converted_at')
            ->where('converted_at', '<', $now->startOfMonth())
            ->count();
        $lastConversionRate = $lastTotalLeads > 0 ? round(($lastConverted / $lastTotalLeads) * 100, 1) : 0;

        $pipelineValue = (int) Lead::query()
            ->whereNull('converted_at')
            ->sum('estimated_value');
        $lastPipelineValue = (int) Lead::query()
            ->whereNull('converted_at')
            ->where('created_at', '<', $now->startOfMonth())
            ->sum('estimated_value');

        return [
            [
                'label' => 'Total Clients',
                'value' => (string) $totalClients,
                'trend' => $this->trend($totalClients, $lastMonthClients),
                'icon' => '🏢',
            ],
            [
                'label' => 'New Leads (MTD)',
                'value' => (string) $newLeads,
                'trend' => $this->trend($newLeads, $lastMonthLeads),
                'icon' => '👋',
            ],
            [
                'label' => 'Conversion Rate',
                'value' => $conversionRate . '%',
                'trend' => ['pct' => round($conversionRate - $lastConversionRate, 1), 'up' => $conversionRate >= $lastConversionRate],
                'icon' => '🔄',
            ],
            [
                'label' => 'Pipeline Value',
                'value' => number_format($pipelineValue, 0),
                'trend' => $this->trend($pipelineValue, $lastPipelineValue),
                'icon' => '💰',
            ],
        ];
    }

    protected function projectKpis(): array
    {
        $now = now();

        $activeStatuses = ['not_started', 'in_progress', 'on_hold'];
        $activeProjects = Project::query()->whereIn('status', $activeStatuses)->count();
        $lastActiveProjects = Project::query()
            ->whereIn('status', $activeStatuses)
            ->where('created_at', '<', $now->startOfMonth())
            ->count();

        $avgProgress = (float) Project::query()
            ->whereIn('status', $activeStatuses)
            ->avg('progress_pct') ?? 0;
        $maxProgress = Project::query()->whereIn('status', $activeStatuses)->max('progress_pct') ?? 0;

        $overdueTasks = Task::query()
            ->where('due_date', '<', $now)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->count();
        $lastOverdueTasks = Task::query()
            ->where('due_date', '<', $now)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->where('created_at', '<', $now->startOfMonth())
            ->count();

        $completedThisMonth = Task::query()
            ->where('status', 'done')
            ->whereMonth('completed_at', $now->month)
            ->whereYear('completed_at', $now->year)
            ->count();
        $completedLastMonth = Task::query()
            ->where('status', 'done')
            ->whereMonth('completed_at', $now->copy()->subMonth()->month)
            ->whereYear('completed_at', $now->copy()->subMonth()->year)
            ->count();

        return [
            [
                'label' => 'Active Projects',
                'value' => (string) $activeProjects,
                'trend' => $this->trend($activeProjects, $lastActiveProjects),
                'icon' => '📋',
            ],
            [
                'label' => 'Avg Progress',
                'value' => round($avgProgress, 1) . '%',
                'trend' => $maxProgress > 0
                    ? ['pct' => round($avgProgress, 1), 'up' => $avgProgress >= 50]
                    : null,
                'icon' => '📈',
            ],
            [
                'label' => 'Overdue Tasks',
                'value' => (string) $overdueTasks,
                'trend' => $this->trend($overdueTasks, $lastOverdueTasks, true),
                'icon' => '⚠️',
            ],
            [
                'label' => 'Completed (MTD)',
                'value' => (string) $completedThisMonth,
                'trend' => $this->trend($completedThisMonth, $completedLastMonth),
                'icon' => '✅',
            ],
        ];
    }

    protected function supportKpis(): array
    {
        $now = now();

        $openTickets = Ticket::query()
            ->whereNull('resolved_at')
            ->whereNull('closed_at')
            ->count();
        $lastOpenTickets = Ticket::query()
            ->whereNull('resolved_at')
            ->whereNull('closed_at')
            ->where('created_at', '<', $now->startOfMonth())
            ->count();

        $avgResponseMinutes = Ticket::query()
            ->whereNotNull('first_response_at')
            ->whereMonth('resolved_at', $now->month)
            ->whereYear('resolved_at', $now->year)
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, first_response_at)) as avg_minutes')
            ->value('avg_minutes');
        $avgResponseHours = $avgResponseMinutes ? round((float) $avgResponseMinutes / 60, 1) : null;

        $lastAvgResponseMinutes = Ticket::query()
            ->whereNotNull('first_response_at')
            ->whereMonth('resolved_at', $now->copy()->subMonth()->month)
            ->whereYear('resolved_at', $now->copy()->subMonth()->year)
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, first_response_at)) as avg_minutes')
            ->value('avg_minutes');
        $lastAvgResponseHours = $lastAvgResponseMinutes ? round((float) $lastAvgResponseMinutes / 60, 1) : null;

        $slaCompliant = Ticket::query()
            ->whereNotNull('first_response_at')
            ->whereNotNull('first_response_due_at')
            ->whereMonth('resolved_at', $now->month)
            ->whereYear('resolved_at', $now->year)
            ->whereRaw('first_response_at <= first_response_due_at')
            ->count();
        $slaTotal = Ticket::query()
            ->whereNotNull('first_response_at')
            ->whereNotNull('first_response_due_at')
            ->whereMonth('resolved_at', $now->month)
            ->whereYear('resolved_at', $now->year)
            ->count();
        $slaRate = $slaTotal > 0 ? round(($slaCompliant / $slaTotal) * 100, 1) : 100;

        $lastSlaCompliant = Ticket::query()
            ->whereNotNull('first_response_at')
            ->whereNotNull('first_response_due_at')
            ->whereMonth('resolved_at', $now->copy()->subMonth()->month)
            ->whereYear('resolved_at', $now->copy()->subMonth()->year)
            ->whereRaw('first_response_at <= first_response_due_at')
            ->count();
        $lastSlaTotal = Ticket::query()
            ->whereNotNull('first_response_at')
            ->whereNotNull('first_response_due_at')
            ->whereMonth('resolved_at', $now->copy()->subMonth()->month)
            ->whereYear('resolved_at', $now->copy()->subMonth()->year)
            ->count();
        $lastSlaRate = $lastSlaTotal > 0 ? round(($lastSlaCompliant / $lastSlaTotal) * 100, 1) : 100;

        $resolvedThisMonth = Ticket::query()
            ->whereMonth('resolved_at', $now->month)
            ->whereYear('resolved_at', $now->year)
            ->count();
        $resolvedLastMonth = Ticket::query()
            ->whereMonth('resolved_at', $now->copy()->subMonth()->month)
            ->whereYear('resolved_at', $now->copy()->subMonth()->year)
            ->count();

        return [
            [
                'label' => 'Open Tickets',
                'value' => (string) $openTickets,
                'trend' => $this->trend($openTickets, $lastOpenTickets, true),
                'icon' => '🎫',
            ],
            [
                'label' => 'Avg First Response',
                'value' => $avgResponseHours !== null ? $avgResponseHours . 'h' : '—',
                'trend' => $avgResponseHours !== null && $lastAvgResponseHours !== null
                    ? [
                        'pct' => round($lastAvgResponseHours - $avgResponseHours, 1),
                        'up' => $avgResponseHours <= $lastAvgResponseHours,
                    ]
                    : null,
                'icon' => '⏱️',
            ],
            [
                'label' => 'SLA Compliance',
                'value' => $slaRate . '%',
                'trend' => ['pct' => round($slaRate - $lastSlaRate, 1), 'up' => $slaRate >= $lastSlaRate],
                'icon' => '🛡️',
            ],
            [
                'label' => 'Resolved (MTD)',
                'value' => (string) $resolvedThisMonth,
                'trend' => $this->trend($resolvedThisMonth, $resolvedLastMonth),
                'icon' => '✅',
            ],
        ];
    }

    protected function trend(int $current, int $previous, bool $invert = false): ?array
    {
        if ($previous === 0 && $current === 0) {
            return null;
        }
        if ($previous === 0) {
            return ['pct' => 100, 'up' => true];
        }
        $pct = round((($current - $previous) / $previous) * 100, 1);
        $up = $invert ? $pct <= 0 : $pct >= 0;
        return ['pct' => $pct, 'up' => $up];
    }
}
