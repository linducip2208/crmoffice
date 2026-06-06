<?php

namespace App\Providers;

use App\Events\ContractExpiring;
use App\Events\InvoiceOverdue;
use App\Events\InvoicePaid;
use App\Events\LeadCreated;
use App\Events\SlaBreached;
use App\Events\TaskAssigned;
use App\Events\TicketOpened;
use App\Listeners\AutoTagTicket;
use App\Listeners\DispatchWebhookForEvent;
use App\Listeners\ScoreLeadWithAi;
use App\Listeners\SendDomainNotifications;
use App\Models\Contract;
use App\Models\CreditNote;
use App\Models\EstimateItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Observers\AuditObserver;
use App\Observers\EstimateItemObserver;
use App\Observers\InvoiceItemObserver;
use App\Observers\PaymentObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\LicenseClient::class);
    }

    public function boot(): void
    {
        $this->configureRateLimiters();

        // Total recalc observers
        InvoiceItem::observe(InvoiceItemObserver::class);
        EstimateItem::observe(EstimateItemObserver::class);
        Payment::observe(PaymentObserver::class);

        // Audit log for financial + RBAC + provider changes
        foreach ([Invoice::class, Payment::class, CreditNote::class, Contract::class, Provider::class, User::class] as $model) {
            $model::observe(AuditObserver::class);
        }

        // Event → Webhook wiring
        Event::listen(InvoicePaid::class, [DispatchWebhookForEvent::class, 'handleInvoicePaid']);
        Event::listen(InvoiceOverdue::class, [DispatchWebhookForEvent::class, 'handleInvoiceOverdue']);
        Event::listen(ContractExpiring::class, [DispatchWebhookForEvent::class, 'handleContractExpiring']);
        Event::listen(SlaBreached::class, [DispatchWebhookForEvent::class, 'handleSlaBreached']);
        Event::listen(TaskAssigned::class, [DispatchWebhookForEvent::class, 'handleTaskAssigned']);
        Event::listen(LeadCreated::class, [DispatchWebhookForEvent::class, 'handleLeadCreated']);
        Event::listen(TicketOpened::class, [DispatchWebhookForEvent::class, 'handleTicketOpened']);

        // Event → Domain notification wiring
        Event::listen(InvoicePaid::class, [SendDomainNotifications::class, 'handleInvoicePaid']);
        Event::listen(InvoiceOverdue::class, [SendDomainNotifications::class, 'handleInvoiceOverdue']);
        Event::listen(ContractExpiring::class, [SendDomainNotifications::class, 'handleContractExpiring']);
        Event::listen(SlaBreached::class, [SendDomainNotifications::class, 'handleSlaBreached']);
        Event::listen(TaskAssigned::class, [SendDomainNotifications::class, 'handleTaskAssigned']);
        Event::listen(LeadCreated::class, [SendDomainNotifications::class, 'handleLeadCreated']);
        Event::listen(TicketOpened::class, [SendDomainNotifications::class, 'handleTicketOpened']);
        Event::listen(TicketOpened::class, [AutoTagTicket::class, 'handle']);

        // Lead scoring via AI
        Event::listen(LeadCreated::class, [ScoreLeadWithAi::class, 'handle']);

        // Auto-fire model created / lifecycle events
        Lead::created(fn ($lead) => LeadCreated::dispatch($lead));
        Ticket::created(fn ($ticket) => TicketOpened::dispatch($ticket));
        Task::created(fn ($task) => TaskAssigned::dispatch($task));
    }

    private function configureRateLimiters(): void
    {
        // Default API throttle: 100 / minute per authenticated user, fallback to IP
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(100)
            ->by(optional($request->user())->id ?: $request->ip()));

        // Authentication endpoints (60 attempts per minute per IP)
        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(60)
            ->by($request->ip()));

        // Public form submissions (contact, newsletter, web-to-lead) — 30/min per IP
        RateLimiter::for('public-form', fn (Request $request) => Limit::perMinute(30)
            ->by($request->ip()));

        // Inbound webhooks (signed but still rate-bounded against floods)
        RateLimiter::for('webhook', fn (Request $request) => Limit::perMinute(300)
            ->by($request->ip()));
    }
}
