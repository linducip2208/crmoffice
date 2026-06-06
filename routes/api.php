<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\Public\LeadIntakeController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API v1 — Sanctum-authenticated routes for future Flutter app + integrators

Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::get('/health', fn () => ['ok' => true, 'app' => 'crmoffice', 'version' => '0.1.0']);

    // Staff auth
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:auth');
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::get('/me', [AuthController::class, 'me'])->name('me');
        });
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::get('/user', fn (Request $r) => $r->user());
        Route::get('/search', [SearchController::class, 'index'])->name('search');

        Route::apiResource('clients', ClientController::class);
        Route::apiResource('contacts', ContactController::class)->except(['show']);
        Route::apiResource('leads', LeadController::class);
        Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');

        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('tasks', TaskController::class);

        Route::apiResource('invoices', InvoiceController::class)->except(['update']);
        Route::apiResource('payments', PaymentController::class)->only(['index', 'store']);

        Route::apiResource('tickets', TicketController::class)->except(['destroy']);
    });

    // Customer portal API (mobile app)
    Route::prefix('portal')->name('portal.')->group(function () {
        Route::post('/auth/login', [\App\Http\Controllers\Api\Portal\PortalAuthController::class, 'login'])
            ->middleware('throttle:auth')
            ->name('auth.login');

        Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
            Route::post('/auth/logout', [\App\Http\Controllers\Api\Portal\PortalAuthController::class, 'logout'])->name('auth.logout');
            Route::get('/me', [\App\Http\Controllers\Api\Portal\PortalAuthController::class, 'me'])->name('me');

            Route::get('/invoices', [\App\Http\Controllers\Api\Portal\PortalInvoiceController::class, 'index'])->name('invoices.index');
            Route::get('/invoices/{invoice}', [\App\Http\Controllers\Api\Portal\PortalInvoiceController::class, 'show'])->name('invoices.show');

            Route::get('/projects', [\App\Http\Controllers\Api\Portal\PortalProjectController::class, 'index'])->name('projects.index');
            Route::get('/projects/{project}', [\App\Http\Controllers\Api\Portal\PortalProjectController::class, 'show'])->name('projects.show');

            Route::get('/tickets', [\App\Http\Controllers\Api\Portal\PortalTicketController::class, 'index'])->name('tickets.index');
            Route::get('/tickets/{ticket}', [\App\Http\Controllers\Api\Portal\PortalTicketController::class, 'show'])->name('tickets.show');
            Route::post('/tickets', [\App\Http\Controllers\Api\Portal\PortalTicketController::class, 'store'])->name('tickets.store');
            Route::post('/tickets/{ticket}/reply', [\App\Http\Controllers\Api\Portal\PortalTicketController::class, 'reply'])->name('tickets.reply');
        });
    });

    // Public (no auth)
    Route::prefix('public')->name('public.')->group(function () {
        Route::post('/leads', [LeadIntakeController::class, 'store'])
            ->name('leads.store')
            ->middleware('throttle:public-form');
    });
});
