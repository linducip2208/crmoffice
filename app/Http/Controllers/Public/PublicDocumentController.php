<?php

namespace App\Http\Controllers\Public;

use App\Adapters\Payment\PaymentAdapterFactory;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\Proposal;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class PublicDocumentController extends Controller
{
    public function showInvoice(string $token)
    {
        $invoice = Invoice::where('public_token', $token)->firstOrFail();
        $invoice->load(['client', 'currency', 'items.taxRate', 'payments']);
        $invoice->updateQuietly(['viewed_at' => $invoice->viewed_at ?? now()]);

        $canPay = $invoice->balance_due > 0
            && ! in_array($invoice->status, ['paid', 'void'])
            && PaymentAdapterFactory::active() !== null;

        return view('public.invoice', [
            'invoice' => $invoice,
            'appName' => Setting::get('app_name', 'crmoffice'),
            'canPay' => $canPay,
        ]);
    }

    public function payInvoice(string $token)
    {
        $invoice = Invoice::where('public_token', $token)->firstOrFail();
        $invoice->load(['client', 'currency', 'items.taxRate', 'payments']);

        if ($invoice->balance_due <= 0 || in_array($invoice->status, ['paid', 'void'])) {
            return back()->with('error', 'Invoice ini sudah tidak bisa dibayar.');
        }

        $adapter = PaymentAdapterFactory::active();
        if (! $adapter) {
            return back()->with('error', 'Payment gateway belum dikonfigurasi.');
        }

        $provider = Provider::where('type', 'payment')
            ->where('is_active', true)
            ->orderBy('priority')
            ->first();

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->balance_due,
            'currency_id' => $invoice->currency_id,
            'method' => 'gateway',
            'provider_id' => $provider->id,
            'status' => 'pending',
        ]);

        $intent = $adapter->createIntent($invoice, [
            'payment_id' => $payment->id,
            'public_token' => $token,
        ]);

        $payment->update(['transaction_id' => $intent->reference]);

        if ($intent->type === 'redirect' && $intent->redirectUrl) {
            return redirect()->away($intent->redirectUrl);
        }

        return view('public.payment', [
            'invoice' => $invoice,
            'appName' => Setting::get('app_name', 'crmoffice'),
            'intent' => $intent,
            'payment' => $payment,
        ]);
    }

    public function downloadInvoicePdf(string $token, Request $request): Response
    {
        $invoice = Invoice::where('public_token', $token)->firstOrFail();
        $invoice->load(['client', 'currency', 'items.taxRate', 'payments']);

        $locale = $request->query('locale', 'id');

        return Pdf::loadView('pdf.' . $locale . '.invoice', [
            'invoice' => $invoice,
            'appName' => Setting::get('app_name', 'crmoffice'),
        ])->setPaper('a4')->download("invoice-{$invoice->number}.pdf");
    }

    public function downloadEstimatePdf(string $token, Request $request): Response
    {
        $estimate = Estimate::where('public_token', $token)->firstOrFail();
        $estimate->load(['client', 'currency', 'items.taxRate']);

        $locale = $request->query('locale', 'id');

        return Pdf::loadView('pdf.' . $locale . '.estimate', [
            'estimate' => $estimate,
            'appName' => Setting::get('app_name', 'crmoffice'),
        ])->setPaper('a4')->download("estimate-{$estimate->number}.pdf");
    }

    public function showEstimate(string $token)
    {
        $estimate = Estimate::where('public_token', $token)->firstOrFail();
        $estimate->load(['client', 'currency', 'items.taxRate']);

        return view('public.estimate', [
            'estimate' => $estimate,
            'appName' => Setting::get('app_name', 'crmoffice'),
        ]);
    }

    public function acceptEstimate(string $token): RedirectResponse
    {
        $estimate = Estimate::where('public_token', $token)->firstOrFail();
        if (! in_array($estimate->status, ['draft', 'sent'])) {
            return back()->with('error', 'Estimate sudah tidak bisa di-update.');
        }
        $estimate->update(['status' => 'accepted']);

        return back()->with('success', 'Estimate diterima. Tim akan follow up.');
    }

    public function declineEstimate(Request $request, string $token): RedirectResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $estimate = Estimate::where('public_token', $token)->firstOrFail();
        if (! in_array($estimate->status, ['draft', 'sent'])) {
            return back();
        }
        $estimate->update(['status' => 'declined']);

        return back()->with('success', 'Estimate ditolak. Terima kasih atas feedback.');
    }

    public function showProposal(string $token)
    {
        $proposal = Proposal::where('public_token', $token)->firstOrFail();
        $proposal->load(['client', 'lead', 'currency']);

        return view('public.proposal', [
            'proposal' => $proposal,
            'appName' => Setting::get('app_name', 'crmoffice'),
        ]);
    }

    public function acceptProposal(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate([
            'typed_name' => 'required|string|max:180',
            'signature_base64' => 'nullable|string',
        ]);

        $proposal = Proposal::where('public_token', $token)->firstOrFail();
        if (! in_array($proposal->status, ['draft', 'sent'])) {
            throw ValidationException::withMessages(['typed_name' => 'Proposal sudah final.']);
        }

        $proposal->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'accepted_by_name' => $data['typed_name'],
            'accepted_signature' => $data['signature_base64'] ?? null,
            'accepted_ip' => $request->ip(),
        ]);

        return back()->with('success', 'Proposal diterima. Terima kasih.');
    }

    public function showContract(string $token)
    {
        $contract = Contract::where('public_token', $token)->firstOrFail();
        $contract->load(['client', 'currency']);

        return view('public.contract', [
            'contract' => $contract,
            'appName' => Setting::get('app_name', 'crmoffice'),
        ]);
    }

    public function signContract(Request $request, string $token): RedirectResponse
    {
        $data = $request->validate([
            'typed_name' => 'required|string|max:180',
            'signature_base64' => 'nullable|string',
        ]);

        $contract = Contract::where('public_token', $token)->firstOrFail();
        if ($contract->signed_at) {
            throw ValidationException::withMessages(['typed_name' => 'Contract sudah ditandatangani.']);
        }

        $contract->update([
            'status' => 'signed',
            'signed_at' => now(),
            'signed_by_name' => $data['typed_name'],
            'signed_signature' => $data['signature_base64'] ?? null,
            'signed_ip' => $request->ip(),
        ]);

        return back()->with('success', 'Contract berhasil ditandatangani.');
    }
}
