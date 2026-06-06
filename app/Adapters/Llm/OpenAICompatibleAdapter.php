<?php

namespace App\Adapters\Llm;

use App\Models\Provider;
use Illuminate\Support\Facades\Http;

/**
 * Universal OpenAI-compatible LLM adapter.
 *
 * Owner inputs base_url + api_key + model_name in admin UI. Compatible with:
 * OpenAI, DeepSeek, Groq, Mistral, Together, Fireworks, OpenRouter, xAI Grok,
 * Anyscale, Cerebras, Ollama (local), LM Studio, vLLM — anything that speaks the
 * /v1/chat/completions wire format.
 *
 * No vendor names in code. Owner picks model via admin UI.
 */
class OpenAICompatibleAdapter implements LlmAdapterContract
{
    public function __construct(private Provider $provider) {}

    public function chat(array $messages, array $options = []): ChatResponse
    {
        $model = $options['model'] ?? $this->provider->extra_config['default_model'] ?? null;
        if (! $model) {
            throw new \RuntimeException('LLM model not configured. Set in Provider->extra_config[default_model].');
        }

        $headers = array_merge(
            [
                'Authorization' => "Bearer {$this->provider->api_key}",
                'Content-Type' => 'application/json',
            ],
            $this->provider->extra_headers ?? []
        );

        $response = Http::withHeaders($headers)->timeout(60)->post(
            rtrim($this->provider->base_url, '/') . '/v1/chat/completions',
            array_filter([
                'model' => $model,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? null,
                'stream' => false,
            ], fn ($v) => $v !== null)
        );

        $data = $response->json() ?? [];

        return new ChatResponse(
            content: data_get($data, 'choices.0.message.content', ''),
            promptTokens: data_get($data, 'usage.prompt_tokens'),
            completionTokens: data_get($data, 'usage.completion_tokens'),
            model: data_get($data, 'model'),
            raw: $data,
        );
    }

    public function listModels(): array
    {
        try {
            $headers = array_merge(
                [
                    'Authorization' => "Bearer {$this->provider->api_key}",
                ],
                $this->provider->extra_headers ?? []
            );

            $response = Http::withHeaders($headers)->timeout(15)->get(rtrim($this->provider->base_url, '/') . '/v1/models');

            return collect($response->json('data', []))->pluck('id')->all();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
