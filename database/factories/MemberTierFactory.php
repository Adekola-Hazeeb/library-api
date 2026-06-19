<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MemberTierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'             => fake()->randomElement(['Regular', 'Premium']),
            'max_books'        => fake()->randomElement([3, 6]),
            'loan_period_days' => fake()->randomElement([14, 21]),
            'fine_rate'        => 50.00,
        ];
    }
}