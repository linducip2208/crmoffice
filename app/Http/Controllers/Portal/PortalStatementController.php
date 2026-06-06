<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PortalStatementController extends Controller
{
    public function index(Request $request): View
    {
        $contact = Auth::guard('portal')->user();
        $client = $contact->client;

        $invoices = $client->invoices()
            ->whereIn('status', ['sent', 'partial', 'overdue', 'paid'])
            ->where('balance_due', '>', 0)
            ->orderBy('due_date')
            ->get(['id', 'number', 'invoice_date', 'due_date', 'total', 'paid_total', 'balance_due', 'status', 'currency_id']);

        $now = now()->startOfDay();

        $buckets = [
            'current' => collect(),
            '1_30' => collect(),
            '31_60' => collect(),
            '61_90' => collect(),
            'over_90' => collect(),
        ];

        foreach ($invoices as $inv) {
            if (! $inv->due_date) {
                $buckets['current']->push($inv);
                continue;
            }

            $daysOverdue = (int) $now->diffInDays($inv->due_date, false);

            if ($daysOverdue <= 0) {
                $buckets['current']->push($inv);
            } elseif ($daysOverdue <= 30) {
                $buckets['1_30']->push($inv);
            } elseif ($daysOverdue <= 60) {
                $buckets['31_60']->push($inv);
            } elseif ($daysOverdue <= 90) {
                $buckets['61_90']->push($inv);
            } else {
                $buckets['over_90']->push($inv);
            }
        }

        $totals = array_map(fn ($bucket) => $bucket->sum('balance_due'), $buckets);
        $grandTotal = array_sum($totals);

        return view('portal.statement', [
            'client' => $client,
            'buckets' => $buckets,
            'totals' => $totals,
            'grandTotal' => $grandTotal,
        ]);
    }
}
