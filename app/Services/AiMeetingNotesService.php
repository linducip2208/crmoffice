<?php

namespace App\Services;

use App\Adapters\Llm\LlmAdapterFactory;
use App\Models\Activity;
use App\Models\Task;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class AiMeetingNotesService
{
    /**
     * Transcribe raw meeting text into structured notes using LLM.
     */
    public function transcribeToNotes(string $rawText, array $context = []): array
    {
        $default = [
            'summary' => '',
            'action_items' => [],
            'decisions' => [],
            'attendees_mentioned' => [],
            'next_meeting' => null,
            'tags' => [],
        ];

        $adapter = LlmAdapterFactory::active();
        if (! $adapter) {
            return $default;
        }

        $config = $this->featureConfig('meeting_notes');
        if (! $this->featureEnabled($config)) {
            return $default;
        }

        $contextBlock = '';
        if (! empty($context['related_name'])) {
            $contextBlock .= "Entitas terkait: {$context['related_name']}";
            if (! empty($context['related_type'])) {
                $contextBlock .= " ({$context['related_type']})";
            }
            $contextBlock .= "\n";
        }
        if (! empty($context['extra'])) {
            $contextBlock .= "Konteks tambahan: {$context['extra']}\n";
        }

        $systemPrompt = <<<'PROMPT'
You are an AI meeting note transcriber. Convert raw meeting notes/transcript into structured JSON in Indonesian.

Rules:
- summary: Ringkasan meeting dalam 3-5 kalimat bahasa Indonesia.
- action_items: Array of objects with keys: task (string), assignee_hint (string or null), deadline_hint (string or null), priority (one of: high|medium|low).
- decisions: Array of strings, each a keputusan yang dibuat dalam meeting.
- attendees_mentioned: Array of strings, nama-nama yang disebut dalam teks.
- next_meeting: String saran jadwal meeting berikutnya, atau null jika tidak ada.
- tags: Array of string tags (lowercase, no spaces, use hyphens).

Respond ONLY with valid JSON. No markdown, no explanation.
PROMPT;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "{$contextBlock}\nRaw meeting notes:\n{$rawText}"],
        ];

        try {
            $options = [];
            if (! empty($config['model'])) {
                $options['model'] = $config['model'];
            }

            $response = $adapter->chat($messages, $options);
            $parsed = $this->extractJson($response->content);

            if (is_array($parsed)) {
                return array_merge($default, array_intersect_key($parsed, $default));
            }

            Log::warning('AiMeetingNotesService: LLM returned non-JSON response', [
                'content' => mb_substr($response->content, 0, 500),
            ]);

            return $default;
        } catch (\Throwable $e) {
            Log::error('AiMeetingNotesService transcribeToNotes error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return $default;
        }
    }

    /**
     * Create an Activity record from meeting notes with full structured metadata.
     */
    public function createFromMeetingNotes(
        string $rawText,
        string $relatedType,
        int $relatedId,
        array $structuredNotes = [],
        array $options = []
    ): Activity {
        if (empty($structuredNotes)) {
            $context = $this->buildContext($relatedType, $relatedId);
            $structuredNotes = $this->transcribeToNotes($rawText, $context);
        }

        $morphMap = $this->morphTypeMap();

        $activity = new Activity();
        $activity->subject_type = $morphMap[$relatedType] ?? $relatedType;
        $activity->subject_id = $relatedId;
        $activity->type = 'meeting';
        $activity->subject = 'Meeting — ' . ($structuredNotes['summary']
            ? mb_substr($structuredNotes['summary'], 0, 100)
            : 'Catatan meeting');
        $activity->description = $rawText;
        $activity->occurred_at = now();
        $activity->user_id = auth()->id();
        $activity->metadata = [
            'raw_text' => $rawText,
            'summary' => $structuredNotes['summary'] ?? '',
            'action_items' => $structuredNotes['action_items'] ?? [],
            'decisions' => $structuredNotes['decisions'] ?? [],
            'attendees_mentioned' => $structuredNotes['attendees_mentioned'] ?? [],
            'next_meeting' => $structuredNotes['next_meeting'] ?? null,
            'tags' => $structuredNotes['tags'] ?? [],
        ];
        $activity->save();

        if (! empty($options['create_tasks']) && ! empty($structuredNotes['action_items'])) {
            $this->createTasksFromActionItems($structuredNotes['action_items'], $relatedType, $relatedId);
        }

        return $activity;
    }

    /**
     * Create Task records from action items extracted from meeting notes.
     */
    public function createTasksFromActionItems(array $actionItems, string $relatedType, int $relatedId): array
    {
        $tasks = [];
        $projectId = null;

        if ($relatedType === 'project') {
            $projectId = $relatedId;
        }

        foreach ($actionItems as $item) {
            $task = new Task();
            $task->title = $item['task'] ?? 'Untitled Task';
            $task->description = 'Auto-generated from meeting notes.';
            $task->priority = $item['priority'] ?? 'medium';
            $task->status = 'todo';
            $task->created_by = auth()->id();

            if ($projectId) {
                $task->project_id = $projectId;
            }

            $task->save();
            $tasks[] = $task;
        }

        return $tasks;
    }

    private function buildContext(string $relatedType, int $relatedId): array
    {
        $context = [];

        try {
            $class = $this->modelClass($relatedType);
            if ($class && class_exists($class)) {
                $record = $class::find($relatedId);
                if ($record) {
                    $context['related_name'] = $record->name ?? $record->company_name ?? $record->title ?? 'Unknown';
                    $context['related_type'] = $relatedType;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('AiMeetingNotesService buildContext error: ' . $e->getMessage());
        }

        return $context;
    }

    private function modelClass(string $type): ?string
    {
        return match ($type) {
            'lead' => \App\Models\Lead::class,
            'client' => \App\Models\Client::class,
            'project' => \App\Models\Project::class,
            default => null,
        };
    }

    private function morphTypeMap(): array
    {
        return [
            'lead' => \App\Models\Lead::class,
            'client' => \App\Models\Client::class,
            'project' => \App\Models\Project::class,
        ];
    }

    private function featureConfig(string $key): ?array
    {
        $settings = Setting::get('ai_features', []);

        return $settings[$key] ?? null;
    }

    private function featureEnabled(?array $config): bool
    {
        return ! empty($config['enabled']) && ! empty($config['provider_id']);
    }

    private function extractJson(string $content): ?array
    {
        $content = trim($content);

        if (preg_match('/```(?:json)?\s*\n?(.*?)\n?```/s', $content, $m)) {
            $content = $m[1];
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : null;
    }
}
