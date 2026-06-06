<?php

namespace App\Services;

use App\Adapters\Llm\LlmAdapterFactory;
use App\Models\Provider;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class AiService
{
    public function isAvailable(): bool
    {
        return LlmAdapterFactory::active() !== null;
    }

    public function providerName(): ?string
    {
        $provider = Provider::where('type', 'llm')
            ->where('is_active', true)
            ->orderBy('priority')
            ->first();

        return $provider?->name;
    }

    public function draftProposal(array $context): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $config = $this->featureConfig('proposal_drafting');
        if (! $this->featureEnabled($config)) {
            return null;
        }

        $messages = [
            ['role' => 'system', 'content' => $this->proposalSystemPrompt()],
            ['role' => 'user', 'content' => json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)],
        ];

        return $this->chat($messages, $config);
    }

    public function classifyTicket(string $subject, string $body): array
    {
        $fallback = [
            'tags' => [],
            'priority_suggestion' => 'medium',
            'sentiment' => 'neutral',
        ];

        if (! $this->isAvailable()) {
            return $fallback;
        }

        $config = $this->featureConfig('ticket_classify');
        if (! $this->featureEnabled($config)) {
            return $fallback;
        }

        $messages = [
            ['role' => 'system', 'content' => 'You are a support ticket classifier. Respond ONLY with valid JSON. Format: {"tags":["tag1","tag2"],"priority_suggestion":"low|medium|high|urgent","sentiment":"positive|neutral|negative"}'],
            ['role' => 'user', 'content' => "Subject: {$subject}\n\nBody: {$body}"],
        ];

        $result = $this->chat($messages, $config);

        if ($result === null) {
            return $fallback;
        }

        $parsed = $this->extractJson($result);

        return is_array($parsed) ? array_merge($fallback, $parsed) : $fallback;
    }

    public function summarizeThread(string $subject, array $replies): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $config = $this->featureConfig('thread_summarize');
        if (! $this->featureEnabled($config)) {
            return null;
        }

        $threadText = collect($replies)
            ->map(fn ($reply, $i) => "Reply #" . ($i + 1) . ":\n" . $reply)
            ->implode("\n\n");

        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful assistant that summarizes support ticket threads. Provide a concise bullet-point summary in Indonesian. Focus on: main issue, steps taken, current status, next action needed.'],
            ['role' => 'user', 'content' => "Ticket Subject: {$subject}\n\nConversation:\n{$threadText}\n\nSummarize this thread."],
        ];

        return $this->chat($messages, $config);
    }

    public function suggestKbArticles(string $subject, string $body): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $config = $this->featureConfig('kb_suggest');
        if (! $this->featureEnabled($config)) {
            return null;
        }

        $messages = [
            ['role' => 'system', 'content' => 'You are a knowledge base assistant. Based on the ticket subject and body, suggest 3 relevant knowledge base article topics that would help resolve this issue. Output as a numbered list with a short title and one-sentence description for each in Indonesian.'],
            ['role' => 'user', 'content' => "Subject: {$subject}\n\nBody: {$body}"],
        ];

        return $this->chat($messages, $config);
    }

    public function draftReply(string $subject, array $thread, string $tone): ?string
    {
        if (! $this->isAvailable()) {
            return null;
        }

        $config = $this->featureConfig('reply_draft');
        if (! $this->featureEnabled($config)) {
            return null;
        }

        $toneInstruction = match ($tone) {
            'formal' => 'Use formal, polite Indonesian language.',
            'friendly' => 'Use warm, friendly Indonesian language.',
            'technical' => 'Use precise, technical language with relevant details.',
            'urgent' => 'Convey urgency while remaining professional.',
            default => 'Use professional, helpful Indonesian language.',
        };

        $threadText = collect($thread)
            ->map(fn ($msg, $i) => "[" . ($msg['role'] ?? 'unknown') . "]: " . ($msg['content'] ?? ''))
            ->implode("\n\n");

        $messages = [
            ['role' => 'system', 'content' => "You are a helpful customer support assistant. Draft a reply to this ticket thread. {$toneInstruction} Be empathetic, address the customer's concerns, and provide clear next steps."],
            ['role' => 'user', 'content' => "Ticket Subject: {$subject}\n\nThread:\n{$threadText}\n\nDraft a reply."],
        ];

        return $this->chat($messages, $config);
    }

    private function proposalSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a professional proposal writer for a CRM consultancy. Write compelling, well-structured business proposals in Indonesian.

Guidelines:
- Start with an executive summary
- Include: problem statement, proposed solution, timeline, pricing breakdown, terms
- Use professional business language
- Be specific about deliverables and scope
- Include a clear call to action
- Format with sections and bullet points where appropriate
PROMPT;
    }

    private function chat(array $messages, ?array $config): ?string
    {
        try {
            $adapter = LlmAdapterFactory::active();
            if (! $adapter) {
                return null;
            }

            $options = [];
            if (! empty($config['model'])) {
                $options['model'] = $config['model'];
            }

            $response = $adapter->chat($messages, $options);

            return $response->content ?: null;
        } catch (\Throwable $e) {
            Log::error('AiService chat error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return null;
        }
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
