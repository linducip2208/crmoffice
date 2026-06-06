<?php

namespace App\Listeners;

use App\Events\ContractExpiring;
use App\Events\InvoiceOverdue;
use App\Events\InvoicePaid;
use App\Events\LeadCreated;
use App\Events\SlaBreached;
use App\Events\TaskAssigned;
use App\Events\TicketOpened;
use App\Services\WebhookDispatcher;

class DispatchWebhookForEvent
{
    public function __construct(private WebhookDispatcher $dispatcher) {}

    public function handleInvoicePaid(InvoicePaid $event): void
    {
        $this->dispatcher->fire('invoice.paid', [
            'invoice' => $this->invoicePayload($event->invoice),
            'payment' => $event->payment ? [
                'id' => $event->payment->id,
                'amount' => (float) $event->payment->amount,
                'method' => $event->payment->method,
                'transaction_id' => $event->payment->transaction_id,
                'paid_at' => $event->payment->paid_at?->toIso8601String(),
            ] : null,
        ]);
    }

    public function handleInvoiceOverdue(InvoiceOverdue $event): void
    {
        $this->dispatcher->fire('invoice.overdue', [
            'invoice' => $this->invoicePayload($event->invoice),
        ]);
    }

    public function handleContractExpiring(ContractExpiring $event): void
    {
        $contract = $event->contract;
        $this->dispatcher->fire('contract.expiring', [
            'id' => $contract->id,
            'number' => $contract->number,
            'subject' => $contract->subject,
            'client_id' => $contract->client_id,
            'start_date' => $contract->start_date?->toIso8601String(),
            'end_date' => $contract->end_date?->toIso8601String(),
            'contract_value' => (float) $contract->contract_value,
            'currency' => $contract->currency?->code,
            'status' => $contract->status,
        ]);
    }

    public function handleSlaBreached(SlaBreached $event): void
    {
        $ticket = $event->ticket;
        $this->dispatcher->fire('ticket.sla_breached', [
            'id' => $ticket->id,
            'number' => $ticket->number,
            'subject' => $ticket->subject,
            'client_id' => $ticket->client_id,
            'priority' => $ticket->priority?->name,
            'department' => $ticket->department?->name,
            'assigned_to' => $ticket->assigned_to,
            'first_response_due_at' => $ticket->first_response_due_at?->toIso8601String(),
            'resolve_due_at' => $ticket->resolve_due_at?->toIso8601String(),
        ]);
    }

    public function handleTaskAssigned(TaskAssigned $event): void
    {
        $task = $event->task;
        $this->dispatcher->fire('task.assigned', [
            'id' => $task->id,
            'title' => $task->title,
            'project_id' => $task->project_id,
            'priority' => $task->priority,
            'status' => $task->status,
            'due_date' => $task->due_date?->toIso8601String(),
            'created_by' => $task->created_by,
            'assignee_ids' => $task->assignees()->pluck('id')->toArray(),
        ]);
    }

    public function handleLeadCreated(LeadCreated $event): void
    {
        $this->dispatcher->fire('lead.created', [
            'id' => $event->lead->id,
            'name' => $event->lead->name,
            'company' => $event->lead->company,
            'email' => $event->lead->email,
            'phone' => $event->lead->phone,
            'source' => $event->lead->source?->name,
            'status' => $event->lead->status?->name,
            'estimated_value' => (float) ($event->lead->estimated_value ?? 0),
            'created_at' => $event->lead->created_at?->toIso8601String(),
        ]);
    }

    public function handleTicketOpened(TicketOpened $event): void
    {
        $this->dispatcher->fire('ticket.opened', [
            'id' => $event->ticket->id,
            'number' => $event->ticket->number,
            'subject' => $event->ticket->subject,
            'client_id' => $event->ticket->client_id,
            'priority' => $event->ticket->priority?->name,
            'department' => $event->ticket->department?->name,
            'created_at' => $event->ticket->created_at?->toIso8601String(),
        ]);
    }

    private function invoicePayload($invoice): array
    {
        return [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'client_id' => $invoice->client_id,
            'total' => (float) $invoice->total,
            'paid_total' => (float) $invoice->paid_total,
            'balance_due' => (float) $invoice->balance_due,
            'currency' => $invoice->currency?->code,
            'status' => $invoice->status,
        ];
    }
}
