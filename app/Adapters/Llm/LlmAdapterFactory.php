<?php

namespace App\Adapters\Llm;

use App\Models\Provider;
use InvalidArgumentException;

class LlmAdapterFactory
{
    public static function for(Provider $provider): LlmAdapterContract
    {
        return match ($provider->api_format) {
            'openai_compatible' => new OpenAICompatibleAdapter($provider),
            'anthropic' => new AnthropicFormatAdapter($provider),
            'gemini' => new GeminiFormatAdapter($provider),
            default => throw new InvalidArgumentException("Unsupported LLM format: {$provider->api_format}"),
        };
    }

    public static function active(): ?LlmAdapterContract
    {
        $provider = Provider::where('type', 'llm')
            ->where('is_active', true)
            ->orderBy('priority')
            ->first();

        return $provider ? self::for($provider) : null;
    }
}
