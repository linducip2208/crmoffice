<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Proposal;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ProposalContractPdfController extends Controller
{
    public function downloadProposal(Proposal $proposal): Response
    {
        return Pdf::loadView('pdf.proposal', [
            'proposal' => $proposal->load(['client', 'lead', 'currency']),
            'appName' => Setting::get('app_name', 'crmoffice'),
        ])->setPaper('a4')->download("proposal-{$proposal->number}.pdf");
    }

    public function downloadContract(Contract $contract): Response
    {
        return Pdf::loadView('pdf.contract', [
            'contract' => $contract->load(['client', 'currency']),
            'appName' => Setting::get('app_name', 'crmoffice'),
        ])->setPaper('a4')->download("contract-{$contract->number}.pdf");
    }
}
