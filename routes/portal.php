<?php

use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalInvoiceController;
use App\Http\Controllers\Portal\PortalProjectController;
use App\Http\Controllers\Portal\PortalStatementController;
use App\Http\Controllers\Portal\PortalTicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')
    ->name('portal.')
    ->middleware(['web'])
    ->group(function () {
        Route::get('/accept-invitation/{token}', [PortalAuthController::class, 'showAccept'])->name('accept.show');
        Route::post('/accept-invitation/{token}', [PortalAuthController::class, 'accept'])->name('accept.store');

        Route::get('/login', [PortalAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [PortalAuthController::class, 'login'])->name('login.attempt');
        Route::post('/logout', [PortalAuthController::class, 'logout'])->name('logout');

        Route::middleware('auth:portal')->group(function () {
            Route::get('/', [PortalDashboardController::class, 'index'])->name('home');

            Route::get('/invoices', [PortalInvoiceController::class, 'index'])->name('invoices.index');
            Route::get('/invoices/{id}', [PortalInvoiceController::class, 'show'])->name('invoices.show');

            Route::get('/projects', [PortalProjectController::class, 'index'])->name('projects.index');
            Route::get('/projects/{id}', [PortalProjectController::class, 'show'])->name('projects.show');

            Route::get('/tickets', [PortalTicketController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/create', [PortalTicketController::class, 'create'])->name('tickets.create');
            Route::post('/tickets', [PortalTicketController::class, 'store'])->name('tickets.store');
            Route::get('/tickets/{id}', [PortalTicketController::class, 'show'])->name('tickets.show');

            Route::get('/statement', [PortalStatementController::class, 'index'])->name('statement');

            Route::post('/gdpr/export', [\App\Http\Controllers\GdprController::class, 'exportData'])->name('gdpr.export');
            Route::post('/gdpr/delete', [\App\Http\Controllers\GdprController::class, 'requestDeletion'])->name('gdpr.delete');
        });
    });
