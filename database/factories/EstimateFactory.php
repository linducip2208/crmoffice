<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Currency;
use App\Models\Estimate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EstimateFactory extends Factory
{
    protected $model = Estimate::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $taxTotal = round($subtotal * 0.11, 2);
        $total = $subtotal + $taxTotal;

        return [
            'number' => 'EST-'.$this->faker->unique()->numberBetween(100000, 999999),
            'client_id' => Client::factory(),
            'estimate_date' => now()->subDays(rand(0, 30)),
            'expiry_date' => now()->addDays(rand(7, 30)),
            'currency_id' => Currency::query()->inRandomOrder()->value('id') ?? Currency::factory(),
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'tax_total' => $taxTotal,
            'total' => $total,
            'status' => $this->faker->randomElement(['draft', 'sent', 'accepted', 'declined', 'expired']),
            'notes' => $this->faker->sentence(),
            'public_token' => Str::random(40),
            'created_by' => User::query()->inRandomOrder()->value('id'),
        ];
    }
}
