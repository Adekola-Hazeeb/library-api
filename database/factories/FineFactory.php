<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class FineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'loan_id'   => Loan::factory(),
            'amount'    => fake()->numberBetween(50, 5000),
            'type'      => 'overdue',
            'is_paid'   => false,
            'paid_at'   => null,
        ];
    }
}