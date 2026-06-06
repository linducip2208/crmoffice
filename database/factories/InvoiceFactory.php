<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 10000);
        $taxTotal = round($subtotal * 0.11, 2);
        $total = $subtotal + $taxTotal;

        return [
            'number' => 'INV-'.$this->faker->unique()->numberBetween(100000, 999999),
            'client_id' => Client::factory(),
            'invoice_date' => now()->subDays(rand(0, 60)),
            'due_date' => now()->addDays(rand(7, 30)),
            'currency_id' => Currency::query()->inRandomOrder()->value('id') ?? Currency::factory(),
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'tax_total' => $taxTotal,
            'total' => $total,
            'paid_total' => 0,
            'balance_due' => $total,
            'status' => $this->faker->randomElement(['draft', 'sent', 'partial', 'paid', 'overdue']),
            'is_recurring' => false,
            'notes' => $this->faker->sentence(),
            'public_token' => Str::random(40),
            'created_by' => User::query()->inRandomOrder()->value('id'),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $a) => [
            'status' => 'paid',
            'paid_total' => $a['total'],
            'balance_due' => 0,
        ]);
    }
}
