<?php

namespace App\Listeners;

use App\Events\ContractExpiring;
use App\Events\InvoiceOverdue;
use App\Events\InvoicePaid;
use App\Events\LeadCreated;
use App\Events\SlaBreached;
use App\Events\TaskAssigned;
use App\Events\TicketOpened;
use App\Models\User;
use App\Notifications\ContractExpiringNotification;
use App\Notifications\InvoiceOverdueNotification;
use App\Notifications\PaymentReceivedNotification;
use App\Notifications\SlaBreachingNotification;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TicketAssignedNotification;
use Illuminate\Support\Facades\Notification;

class SendDomainNotifications
{
    public function handleInvoicePaid(InvoicePaid $event): void
    {
        $invoice = $event->invoice;
        $payment = $event->payment;
        if (! $payment) {
            return;
        }

        $recipients = collect();
        if ($invoice->created_by) {
            $recipients->push(User::find($invoice->created_by));
        }
        $recipients->push(...User::role('owner')->get());

        Notification::send(
            $recipients->filter()->unique('id'),
            new PaymentReceivedNotification($invoice, $payment),
        );
    }

    public function handleInvoiceOverdue(InvoiceOverdue $event): void
    {
        $invoice = $event->invoice;

        $recipients = collect();
        if ($invoice->created_by) {
            $recipients->push(User::find($invoice->created_by));
        }
        $recipients->push(...User::role('owner')->get());
        $recipients->push(...User::role('accountant')->get());

        Notification::send(
            $recipients->filter()->unique('id'),
            new InvoiceOverdueNotification($invoice),
        );
    }

    public function handleContractExpiring(ContractExpiring $event): void
    {
        $contract = $event->contract;

        $recipients = collect();
        if ($contract->created_by) {
            $recipients->push(User::find($contract->created_by));
        }
        $recipients->push(...User::role('owner')->get());

        Notification::send(
            $recipients->filter()->unique('id'),
            new ContractExpiringNotification($contract),
        );
    }

    public function handleSlaBreached(SlaBreached $event): void
    {
        $ticket = $event->ticket;

        $recipients = collect();
        if ($ticket->assigned_to) {
            $recipients->push(User::find($ticket->assigned_to));
        }
        $recipients->push(...User::role('support')->get());
        $recipients->push(...User::role('owner')->get());

        foreach ($recipients->filter()->unique('id') as $user) {
            $user->notify(new SlaBreachingNotification($ticket, 'first_response'));
        }
    }

    public function handleTaskAssigned(TaskAssigned $event): void
    {
        $task = $event->task;

        $assignees = $task->assignees()->get();

        Notification::send(
            $assignees->filter()->unique('id'),
            new TaskAssignedNotification($task),
        );
    }

    public function handleTicketOpened(TicketOpened $event): void
    {
        $ticket = $event->ticket;
        if (! $ticket->assigned_to) {
            return;
        }

        $assignee = User::find($ticket->assigned_to);
        if ($assignee) {
            $assignee->notify(new TicketAssignedNotification($ticket));
        }
    }

    public function handleLeadCreated(LeadCreated $event): void
    {
        $lead = $event->lead;
        if (! $lead->assigned_to) {
            return;
        }

        $assignee = User::find($lead->assigned_to);
        if ($assignee) {
            $assignee->notify(new \App\Notifications\GenericInAppNotification(
                title: "New lead assigned: {$lead->name}",
                body: $lead->company,
                url: "/admin/leads/{$lead->id}/edit",
            ));
        }
    }
}
