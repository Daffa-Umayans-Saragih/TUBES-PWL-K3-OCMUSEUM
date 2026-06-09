<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GuestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email'         => $this->faker->unique()->safeEmail(),
            'first_name'    => $this->faker->firstName(),
            'last_name'     => $this->faker->lastName(),
            'session_token' => Str::random(40),
        ];
    }
}
