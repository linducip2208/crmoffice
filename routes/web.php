<?php

use App\Http\Controllers\DocsController;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReceiptPdfController;
use App\Http\Controllers\TwoFactorChallengeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin');
    }

    return response()->view('marketing');
})->name('home');

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:auth')->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/healthz', [\App\Http\Controllers\HealthController::class, 'check'])->name('healthz');

Route::get('/docs', [DocsController::class, 'index'])->name('docs.index');
Route::get('/docs/{slug}', [DocsController::class, 'show'])->where('slug', '[a-z0-9\-]+')->name('docs.show');

// Two-factor challenge (after login, before admin access)
Route::middleware(['web', 'auth', 'throttle:auth'])->group(function () {
    Route::get('/two-factor', [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
    Route::post('/two-factor', [TwoFactorChallengeController::class, 'verify'])->name('two-factor.verify');
});

// Admin-only AI endpoints
Route::middleware(['web', 'auth'])->prefix('admin/ai')->name('admin.ai.')->group(function () {
    Route::post('/chat', [\App\Http\Controllers\AiController::class, 'chat'])->name('chat');
    Route::post('/insight', [\App\Http\Controllers\AiController::class, 'insight'])->name('insight');
    Route::post('/summarize-ticket/{ticket}', [\App\Http\Controllers\AiController::class, 'summarizeTicket'])->name('summarize-ticket');
    Route::post('/suggest-kb', [\App\Http\Controllers\AiController::class, 'suggestKb'])->name('suggest-kb');
    Route::post('/draft-reply/{ticket}', [\App\Http\Controllers\AiController::class, 'draftReply'])->name('draft-reply');
    Route::post('/meeting-notes', [\App\Http\Controllers\AiController::class, 'meetingNotes'])->name('meeting-notes');
});

// Admin-only Lead & Invoice AI endpoints
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/admin/leads/{lead}/score', [\App\Http\Controllers\AiController::class, 'scoreLead'])->name('admin.leads.score');
    Route::post('/admin/invoices/{invoice}/ai-reminder', [\App\Http\Controllers\AiController::class, 'generateInvoiceReminder'])->name('admin.invoices.ai-reminder');
});

// Admin-only PDF endpoints (auth via web guard)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/invoices/{invoice}/pdf', [InvoicePdfController::class, 'download'])->name('invoice.pdf.download');
    Route::get('/admin/invoices/{invoice}/pdf/preview', [InvoicePdfController::class, 'stream'])->name('invoice.pdf.preview');
    Route::get('/admin/proposals/{proposal}/pdf', [\App\Http\Controllers\ProposalContractPdfController::class, 'downloadProposal'])->name('proposal.pdf');
    Route::get('/admin/contracts/{contract}/pdf', [\App\Http\Controllers\ProposalContractPdfController::class, 'downloadContract'])->name('contract.pdf');
    Route::get('/admin/payments/{payment}/receipt', [ReceiptPdfController::class, 'download'])->name('payment.receipt');
});

// ICS Calendar Feed
Route::get('/calendar.ics', [\App\Http\Controllers\IcsFeedController::class, 'feed'])->name('calendar.ics');

require __DIR__.'/portal.php';
require __DIR__.'/public.php';
require __DIR__.'/webhooks.php';
require __DIR__.'/pair-routes.php';
