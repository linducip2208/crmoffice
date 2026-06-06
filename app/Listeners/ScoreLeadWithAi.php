<?php

namespace App\Listeners;

use App\Adapters\Llm\LlmAdapterFactory;
use App\Events\LeadCreated;
use Illuminate\Support\Facades\Log;

class ScoreLeadWithAi
{
    public function handle(LeadCreated $event): void
    {
        $lead = $event->lead;

        $llm = LlmAdapterFactory::active();
        if (! $llm) {
            return;
        }

        try {
            $context = [];
            $context[] = "Name: {$lead->name}";
            if ($lead->company) {
                $context[] = "Company: {$lead->company}";
            }
            if ($lead->email) {
                $context[] = "Email: {$lead->email}";
            }
            if ($lead->phone) {
                $context[] = "Phone: {$lead->phone}";
            }
            if ($lead->industry) {
                $context[] = "Industry: {$lead->industry}";
            }
            if ($lead->website) {
                $context[] = "Website: {$lead->website}";
            }
            if ($lead->estimated_value) {
                $context[] = "Estimated Value: {$lead->estimated_value}";
            }
            if ($lead->description) {
                $context[] = "Description: {$lead->description}";
            }

            $prompt = implode("\n", $context);

            $messages = [
                ['role' => 'system', 'content' => 'You are a lead scoring assistant. Analyze the lead and return only JSON: {"score": 1-10, "reasoning": "brief explanation", "priority": "low|medium|high"}'],
                ['role' => 'user', 'content' => $prompt],
            ];

            $response = $llm->chat($messages, [
                'temperature' => 0.2,
                'max_tokens' => 200,
            ]);

            $json = $this->extractJson($response->content);
            if (! $json) {
                Log::warning('ScoreLeadWithAi: failed to parse LLM response', ['content' => $response->content]);

                return;
            }

            $customFields = $lead->custom_fields ?? [];
            $customFields['ai_score'] = [
                'score' => (int) ($json['score'] ?? 0),
                'reasoning' => $json['reasoning'] ?? '',
                'priority' => $json['priority'] ?? 'medium',
                'scored_at' => now()->toIso8601String(),
            ];

            $lead->update(['custom_fields' => $customFields]);

            Log::info('ScoreLeadWithAi: lead scored', [
                'lead_id' => $lead->id,
                'score' => $json['score'] ?? 0,
                'priority' => $json['priority'] ?? 'medium',
            ]);
        } catch (\Throwable $e) {
            Log::error('ScoreLeadWithAi: failed', [
                'lead_id' => $lead->id,
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
}
