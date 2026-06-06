<?php

namespace App\Adapters\Llm;

use App\Models\Provider;
use Illuminate\Support\Facades\Http;

class GeminiFormatAdapter implements LlmAdapterContract
{
    public function __construct(private Provider $provider) {}

    public function chat(array $messages, array $options = []): ChatResponse
    {
        $model = $options['model'] ?? $this->provider->extra_config['default_model'] ?? null;
        if (! $model) {
            throw new \RuntimeException('LLM model not configured. Set in Provider->extra_config[default_model].');
        }

        $system = collect($messages)->firstWhere('role', 'system');
        $contents = collect($messages)
            ->reject(fn ($m) => $m['role'] === 'system')
            ->map(fn ($m) => [
                'role' => $m['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $m['content']]],
            ])
            ->values()
            ->all();

        $body = array_filter([
            'contents' => $contents,
            'generationConfig' => array_filter([
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['max_tokens'] ?? null,
            ], fn ($v) => $v !== null),
        ]);

        if ($system) {
            $body['systemInstruction'] = [
                'parts' => [['text' => $system['content']]],
            ];
        }

        $headers = array_merge(
            [
                'Content-Type' => 'application/json',
            ],
            $this->provider->extra_headers ?? []
        );

        $url = rtrim($this->provider->base_url, '/') . '/models/' . $model . ':generateContent';

        if ($this->provider->api_key) {
            $url .= '?key=' . $this->provider->api_key;
        }

        $response = Http::withHeaders($headers)
            ->timeout(60)
            ->post($url, $body);

        $data = $response->json() ?? [];

        return new ChatResponse(
            content: data_get($data, 'candidates.0.content.parts.0.text', ''),
            promptTokens: data_get($data, 'usageMetadata.promptTokenCount'),
            completionTokens: data_get($data, 'usageMetadata.candidatesTokenCount'),
            model: data_get($data, 'modelVersion'),
            raw: $data,
        );
    }

    public function listModels(): array
    {
        $url = rtrim($this->provider->base_url, '/') . '/models';

        if ($this->provider->api_key) {
            $url .= '?key=' . $this->provider->api_key;
        }

        try {
            $response = Http::timeout(15)->get($url);

            return collect($response->json('models', []))
                ->pluck('name')
                ->map(fn ($n) => preg_replace('/^models\//', '', $n))
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
