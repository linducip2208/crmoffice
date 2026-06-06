<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'company_name' => $this->faker->company(),
            'industry' => $this->faker->randomElement(['agency', 'saas', 'retail', 'consulting', 'manufacturing', 'real-estate']),
            'website' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'billing_address' => $this->faker->streetAddress(),
            'billing_city' => $this->faker->city(),
            'billing_state' => $this->faker->state(),
            'billing_country' => $this->faker->countryCode(),
            'billing_postal' => $this->faker->postcode(),
            'tax_id' => $this->faker->bothify('TAX-#####'),
            'account_manager_id' => User::factory(),
            'default_currency_id' => Currency::query()->inRandomOrder()->value('id') ?? Currency::factory(),
            'default_language' => 'en',
            'status' => 'active',
            'notes' => $this->faker->sentence(),
        ];
    }
}
