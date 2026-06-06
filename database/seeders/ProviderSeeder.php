<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        if (Provider::where('type', 'llm')->exists()) {
            return;
        }

        $presets = [
            [
                'name' => 'Groq (GRATIS)',
                'type' => 'llm',
                'api_format' => 'openai_compatible',
                'base_url' => 'https://api.groq.com/openai/v1',
                'api_key_encrypted' => null,
                'extra_config' => ['default_model' => 'deepseek-r1-distill-llama-70b'],
                'is_active' => false,
                'priority' => 1,
            ],
            [
                'name' => 'DeepSeek',
                'type' => 'llm',
                'api_format' => 'openai_compatible',
                'base_url' => 'https://api.deepseek.com',
                'api_key_encrypted' => null,
                'extra_config' => ['default_model' => 'deepseek-chat'],
                'is_active' => false,
                'priority' => 2,
            ],
            [
                'name' => 'OpenRouter (model gratis)',
                'type' => 'llm',
                'api_format' => 'openai_compatible',
                'base_url' => 'https://openrouter.ai/api/v1',
                'api_key_encrypted' => null,
                'extra_config' => ['default_model' => 'google/gemini-2.0-flash-001'],
                'is_active' => false,
                'priority' => 3,
            ],
            [
                'name' => 'Google Gemini (gratis)',
                'type' => 'llm',
                'api_format' => 'openai_compatible',
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta/openai',
                'api_key_encrypted' => null,
                'extra_config' => ['default_model' => 'gemini-2.0-flash'],
                'is_active' => false,
                'priority' => 4,
            ],
            [
                'name' => 'Ollama (Local - GRATIS)',
                'type' => 'llm',
                'api_format' => 'openai_compatible',
                'base_url' => 'http://localhost:11434',
                'api_key_encrypted' => null,
                'extra_config' => ['default_model' => 'deepseek-r1:8b'],
                'is_active' => false,
                'priority' => 5,
            ],
        ];

        foreach ($presets as $preset) {
            Provider::create($preset);
        }
    }
}
