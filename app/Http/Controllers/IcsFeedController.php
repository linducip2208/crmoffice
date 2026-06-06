<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IcsFeedController extends Controller
{
    public function feed(Request $request): Response
    {
        $user = User::where('ics_token', $request->query('token'))->first();

        if (! $user) {
            abort(401, 'Invalid calendar token');
        }

        $events = [];

        foreach ($this->getTasks($user) as $task) {
            if ($task->due_date) {
                $events[] = $this->buildVevent(
                    uid: "task-{$task->id}@crmoffice",
                    dtstart: $task->due_date,
                    summary: "[Task] {$task->title}",
                    description: $task->description,
                    status: $task->status,
                );
            }
        }

        foreach ($this->getMilestones($user) as $milestone) {
            if ($milestone->due_date) {
                $events[] = $this->buildVevent(
                    uid: "milestone-{$milestone->id}@crmoffice",
                    dtstart: $milestone->due_date,
                    summary: "[Milestone] {$milestone->name}",
                    description: $milestone->description,
                    status: $milestone->complete_pct >= 100 ? 'COMPLETED' : null,
                );
            }
        }

        foreach ($this->getTicketDeadlines($user) as $ticket) {
            if ($ticket->resolve_due_at) {
                $events[] = $this->buildVevent(
                    uid: "ticket-{$ticket->id}@crmoffice",
                    dtstart: $ticket->resolve_due_at,
                    summary: "[Ticket] {$ticket->subject}",
                    description: "Ticket #{$ticket->number} · Priority: {$ticket->priority?->name}",
                    status: $ticket->status?->is_resolved ? 'COMPLETED' : null,
                );
            }
        }

        $ics = $this->buildIcs($events);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="calendar.ics"',
        ]);
    }

    private function getTasks(User $user): array
    {
        return Task::where('due_date', '>=', now()->subDays(30))
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('assignees', fn ($sq) => $sq->where('user_id', $user->id));
            })
            ->orderBy('due_date')
            ->get()
            ->all();
    }

    private function getMilestones(User $user): array
    {
        return Milestone::where('due_date', '>=', now()->subDays(30))
            ->whereHas('project', function ($q) use ($user) {
                $q->where('project_manager_id', $user->id)
                  ->orWhereHas('tasks.assignees', fn ($sq) => $sq->where('user_id', $user->id));
            })
            ->orderBy('due_date')
            ->get()
            ->all();
    }

    private function getTicketDeadlines(User $user): array
    {
        return Ticket::where('resolve_due_at', '>=', now()->subDays(30))
            ->where('assigned_to', $user->id)
            ->orderBy('resolve_due_at')
            ->get()
            ->all();
    }

    private function buildVevent(string $uid, \DateTimeInterface|string $dtstart, string $summary, ?string $description = null, ?string $status = null): string
    {
        $now = now()->format('Ymd\THis\Z');
        $dtstamp = $now;
        $dtstartFormatted = ($dtstart instanceof \DateTimeInterface)
            ? $dtstart->format('Ymd')
            : date('Ymd', strtotime($dtstart));

        $lines = [
            'BEGIN:VEVENT',
            "DTSTART;VALUE=DATE:$dtstartFormatted",
            "DTSTAMP:$dtstamp",
            "UID:$uid",
            "SUMMARY:" . $this->escape($summary),
        ];

        if ($description) {
            $lines[] = 'DESCRIPTION:' . $this->escape($description);
        }

        if ($status) {
            $lines[] = "STATUS:$status";
        }

        $lines[] = 'END:VEVENT';

        return implode("\r\n", $lines);
    }

    private function buildIcs(array $events): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//crmoffice//calendar//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:CRM Office Calendar',
            'X-WR-TIMEZONE:Asia/Jakarta',
            'REFRESH-INTERVAL;VALUE=DURATION:PT1H',
        ];

        foreach ($events as $event) {
            $lines[] = $event;
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines) . "\r\n";
    }

    private function escape(string $text): string
    {
        return str_replace(
            ['\\', ';', ',', "\n", "\r"],
            ['\\\\', '\\;', '\\,', '\\n', ''],
            $text
        );
    }
}
