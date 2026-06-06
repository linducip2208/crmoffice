<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Currency;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->words(3, true)),
            'description' => $this->faker->paragraph(),
            'client_id' => Client::factory(),
            'project_manager_id' => User::query()->inRandomOrder()->value('id'),
            'start_date' => now()->subDays(rand(0, 30)),
            'deadline' => now()->addDays(rand(30, 120)),
            'estimate_hours' => $this->faker->randomFloat(2, 10, 500),
            'billing_method' => $this->faker->randomElement(['fixed', 'hourly', 'milestone', 'non_billable']),
            'fixed_price' => $this->faker->randomFloat(2, 1000, 25000),
            'hourly_rate' => $this->faker->randomFloat(2, 25, 200),
            'currency_id' => Currency::query()->inRandomOrder()->value('id') ?? Currency::factory(),
            'status' => $this->faker->randomElement(['not_started', 'in_progress', 'completed', 'on_hold']),
            'progress_pct' => $this->faker->numberBetween(0, 100),
            'is_visible_to_customer' => true,
        ];
    }
}
