<?php

namespace Database\Factories;

use App\Models\MemberTier;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'member_tier_id' => MemberTier::factory(),
            'first_name'     => fake()->firstName(),
            'last_name'      => fake()->lastName(),
            'email'          => fake()->unique()->safeEmail(),
            'password'       => 'password123',
            'phone_number'   => fake()->phoneNumber(),
            'status'         => 'active',
        ];
    }
}