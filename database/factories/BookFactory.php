<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'          => fake()->sentence(3),
            /* unique 13-digit ISBN format */
            'isbn'           => fake()->unique()->isbn13(),
            'description'    => fake()->paragraph(),
            'published_year' => fake()->numberBetween(1900, 2024),
            'is_retired'     => false,
        ];
    }
}