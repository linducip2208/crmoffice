<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        $start = now()->subHours(rand(1, 24));
        $end = (clone $start)->addMinutes(rand(15, 240));

        return [
            'user_id' => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'task_id' => Task::factory(),
            'start_at' => $start,
            'end_at' => $end,
            'minutes' => $start->diffInMinutes($end),
            'note' => $this->faker->sentence(),
            'is_billable' => $this->faker->boolean(80),
            'is_invoiced' => false,
        ];
    }
}
