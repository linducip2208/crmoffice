<?php

namespace Database\Factories;

use App\Models\LeadSource;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LeadSourceFactory extends Factory
{
    protected $model = LeadSource::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement(['Web Form', 'Referral', 'Cold Call', 'Trade Show', 'Email Campaign', 'Google Ads']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->randomNumber(4),
            'order' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
