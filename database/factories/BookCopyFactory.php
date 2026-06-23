<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Book;

class BookCopyFactory extends Factory
{
public function definition(): array
{
    return [
        'book_id'     => Book::factory(),
        'copy_number' => fake()->numberBetween(1, 10),
        'condition'   => 'good',
        'status'      => 'available',
    ];
}
}