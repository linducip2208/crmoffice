<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => ucfirst($this->faker->words(4, true)),
            'description' => $this->faker->paragraph(),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'status' => $this->faker->randomElement(['todo', 'in_progress', 'review', 'done']),
            'start_date' => now()->subDays(rand(0, 14)),
            'due_date' => now()->addDays(rand(1, 30)),
            'estimate_hours' => $this->faker->randomFloat(2, 1, 40),
            'is_billable' => $this->faker->boolean(70),
            'hourly_rate' => $this->faker->randomFloat(2, 25, 200),
            'is_visible_to_customer' => $this->faker->boolean(40),
            'order' => $this->faker->numberBetween(0, 100),
            'created_by' => User::query()->inRandomOrder()->value('id'),
        ];
    }
}
