<?php

namespace App\Listeners;

use App\Adapters\Llm\LlmAdapterFactory;
use App\Events\TicketOpened;
use App\Models\TicketPriority;
use Illuminate\Support\Facades\Log;

class AutoTagTicket
{
    public function handle(TicketOpened $event): void
    {
        $ticket = $event->ticket;

        $llm = LlmAdapterFactory::active();
        if (! $llm) {
            return;
        }

        try {
            $prompt = "Subject: {$ticket->subject}\n\nBody: {$ticket->body}";

            $messages = [
                ['role' => 'system', 'content' => 'You are a ticket classifier. Respond ONLY with valid JSON: {"tags": ["bug","feature","billing","how-to","complaint"], "priority_suggestion": "low|medium|high|urgent", "department_suggestion": "technical|billing|general"}'],
                ['role' => 'user', 'content' => $prompt],
            ];

            $response = $llm->chat($messages, [
                'temperature' => 0.2,
                'max_tokens' => 200,
            ]);

            $json = $this->extractJson($response->content);
            if (! $json) {
                Log::warning('AutoTagTicket: failed to parse LLM response', ['content' => $response->content]);

                return;
            }

            $customFields = $ticket->custom_fields ?? [];
            $customFields['ai_tags'] = $json['tags'] ?? [];

            $ticket->update(['custom_fields' => $customFields]);

            if (isset($json['priority_suggestion'])) {
                $this->maybeEscalate($ticket, $json['priority_suggestion']);
            }

            Log::info('AutoTagTicket: ticket classified', [
                'ticket_id' => $ticket->id,
                'tags' => $json['tags'] ?? [],
                'priority_suggestion' => $json['priority_suggestion'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('AutoTagTicket: failed', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function extractJson(string $content): ?array
    {
        $content = trim($content);

        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $content, $matches)) {
            $content = $matches[1];
        }

        $json = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json;
        }

        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        return null;
    }

    private function maybeEscalate(\App\Models\Ticket $ticket, string $suggestedPriority): void
    {
        $priorityOrder = ['low' => 1, 'medium' => 2, 'high' => 3, 'urgent' => 4];

        $currentPriority = $ticket->priority;
        $currentName = $currentPriority ? strtolower($currentPriority->name) : 'low';
        $currentLevel = $priorityOrder[$currentName] ?? 1;
        $suggestedLevel = $priorityOrder[strtolower($suggestedPriority)] ?? 1;

        if ($suggestedLevel <= $currentLevel) {
            return;
        }

        $newPriority = TicketPriority::where('is_active', true)
            ->whereRaw('LOWER(name) = ?', [strtolower($suggestedPriority)])
            ->first();

        if ($newPriority) {
            $ticket->update(['priority_id' => $newPriority->id]);
            Log::info('AutoTagTicket: escalated priority', [
                'ticket_id' => $ticket->id,
                'from' => $currentName,
                'to' => $suggestedPriority,
            ]);
        }
    }
}
