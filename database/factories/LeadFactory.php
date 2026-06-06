<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'company' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'website' => $this->faker->url(),
            'city' => $this->faker->city(),
            'country' => $this->faker->countryCode(),
            'estimated_value' => $this->faker->randomFloat(2, 500, 50000),
            'lead_source_id' => LeadSource::query()->inRandomOrder()->value('id'),
            'lead_status_id' => LeadStatus::query()->inRandomOrder()->value('id') ?? LeadStatus::factory(),
            'assigned_to' => User::query()->inRandomOrder()->value('id'),
            'description' => $this->faker->paragraph(),
            'expected_close' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
            'last_activity_at' => now(),
        ];
    }
}
