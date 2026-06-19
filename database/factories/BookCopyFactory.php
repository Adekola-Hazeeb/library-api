<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookCopyFactory extends Factory
{
    public function definition(): array
    {
        return [
            /* book_id must be provided when using this factory */
            'copy_number' => fake()->numberBetween(1, 10),
            'condition'   => fake()->randomElement(['good', 'damaged', 'worn']),
            'status'      => 'available',
        ];
    }
}