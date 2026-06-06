<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'currency_id' => Currency::query()->inRandomOrder()->value('id') ?? Currency::factory(),
            'paid_at' => now(),
            'method' => $this->faker->randomElement(['bank_transfer', 'cash', 'card', 'qris', 'check']),
            'transaction_id' => $this->faker->bothify('TXN-########'),
            'status' => 'completed',
        ];
    }
}
