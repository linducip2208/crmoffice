<?php

namespace App\Actions\Ai;

use App\Adapters\Llm\LlmAdapterFactory;
use App\Models\Client;
use App\Models\Lead;

class DraftProposalWithAi
{
    public function handle(string $subject, ?int $clientId = null, ?int $leadId = null, string $tone = 'professional', ?string $instructions = null): string
    {
        $llm = LlmAdapterFactory::active();
        if (! $llm) {
            throw new \RuntimeException('No active LLM provider. Configure one in Settings → Providers (type=llm).');
        }

        $context = $this->buildContext($clientId, $leadId);

        $messages = [
            ['role' => 'system', 'content' => "You are a senior sales proposal writer for a digital agency. Write proposals in Indonesian. Tone: $tone. Use clear HTML markup (h2, h3, p, ul, li, strong). Be concrete with deliverables, timeline, and value."],
            ['role' => 'user', 'content' => <<<PROMPT
Draft a proposal with the following details:

Subject: $subject

Context:
$context
{$this->instructionsSection($instructions)}

Required structure:
- Executive Summary (2-3 paragraphs)
- Scope of Work (bullet list of deliverables)
- Timeline (week-by-week or phase-by-phase)
- Investment / Pricing rationale
- Why Us (3 bullet points)
- Next Steps

Return only the HTML body content. No <html>, <head>, <body> tags. No markdown.
PROMPT,
            ],
        ];

        $response = $llm->chat($messages, [
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ]);

        return $response->content;
    }

    private function instructionsSection(?string $instructions): string
    {
        if (! $instructions) {
            return '';
        }

        return "\n\nAdditional Instructions:\n{$instructions}";
    }

    private function buildContext(?int $clientId, ?int $leadId): string
    {
        $parts = [];

        if ($clientId && $client = Client::find($clientId)) {
            $parts[] = "Client: {$client->company_name}";
            if ($client->industry) {
                $parts[] = "Industry: {$client->industry}";
            }
            if ($client->website) {
                $parts[] = "Website: {$client->website}";
            }
        }

        if ($leadId && $lead = Lead::find($leadId)) {
            $parts[] = "Lead: {$lead->name}" . ($lead->company ? " ({$lead->company})" : '');
            if ($lead->description) {
                $parts[] = "Notes: {$lead->description}";
            }
            if ($lead->estimated_value) {
                $parts[] = "Estimated value: Rp " . number_format($lead->estimated_value, 0, ',', '.');
            }
        }

        return implode("\n", $parts) ?: '(no additional context)';
    }
}
