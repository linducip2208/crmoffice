<?php

namespace Database\Factories;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'type' => $this->faker->randomElement(['llm', 'payment', 'mail', 'sms', 'storage']),
            'api_format' => $this->faker->randomElement(['openai_compat', 'smtp', 'redirect_flow', 'rest_api']),
            'base_url' => $this->faker->url(),
            'api_key' => 'test-'.$this->faker->sha1(),
            'extra_headers' => [],
            'extra_config' => [],
            'is_active' => true,
            'priority' => 1,
        ];
    }
}
