<?php

namespace App\Adapters\Llm;

use App\Models\Provider;
use Illuminate\Support\Facades\Http;

class AnthropicFormatAdapter implements LlmAdapterContract
{
    public function __construct(private Provider $provider) {}

    public function chat(array $messages, array $options = []): ChatResponse
    {
        $model = $options['model'] ?? $this->provider->extra_config['default_model'] ?? null;
        if (! $model) {
            throw new \RuntimeException('LLM model not configured.');
        }

        // Anthropic separates "system" from messages
        $system = collect($messages)->firstWhere('role', 'system')['content'] ?? null;
        $msgs = collect($messages)->filter(fn ($m) => $m['role'] !== 'system')->values()->all();

        $headers = array_merge(
            [
                'x-api-key' => $this->provider->api_key,
                'anthropic-version' => $this->provider->extra_config['anthropic_version'] ?? '2023-06-01',
                'Content-Type' => 'application/json',
            ],
            $this->provider->extra_headers ?? []
        );

        $response = Http::withHeaders($headers)->timeout(60)->post(
            rtrim($this->provider->base_url, '/') . '/v1/messages',
            array_filter([
                'model' => $model,
                'system' => $system,
                'messages' => $msgs,
                'max_tokens' => $options['max_tokens'] ?? 1024,
                'temperature' => $options['temperature'] ?? 0.7,
            ], fn ($v) => $v !== null)
        );

        $data = $response->json() ?? [];

        return new ChatResponse(
            content: data_get($data, 'content.0.text', ''),
            promptTokens: data_get($data, 'usage.input_tokens'),
            completionTokens: data_get($data, 'usage.output_tokens'),
            model: data_get($data, 'model'),
            raw: $data,
        );
    }

    public function listModels(): array
    {
        return $this->provider->extra_config['available_models'] ?? [];
    }
}
