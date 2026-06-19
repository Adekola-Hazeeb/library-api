<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            /* unique() prevents duplicate category names failing validation */
            'name'        => fake()->unique()->word(),
            'description' => fake()->sentence(),
        ];
    }
}