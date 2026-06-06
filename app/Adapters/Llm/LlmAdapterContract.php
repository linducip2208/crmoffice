<?php

namespace App\Adapters\Llm;

use App\Models\Provider;

interface LlmAdapterContract
{
    public function __construct(Provider $provider);

    public function chat(array $messages, array $options = []): ChatResponse;

    public function listModels(): array;
}

class ChatResponse
{
    public function __construct(
        public string $content,
        public ?int $promptTokens = null,
        public ?int $completionTokens = null,
        public ?string $model = null,
        public ?array $raw = null,
    ) {}
}
