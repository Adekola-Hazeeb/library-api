<?php

namespace Database\Factories;

use App\Models\BookCopy;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_id'      => Member::factory(),
            'book_copy_id'   => BookCopy::factory(),
            'borrowed_at'    => now(),
            'due_date'       => now()->addDays(14),
            'returned_at'    => null,
            'status'         => 'active',
            'renewals_count' => 0,
            'fines_accrued'  => 0,
        ];
    }
}