<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'location_name'  => $this->faker->company() . ' Wing',
            'address'        => $this->faker->address(),
            'capacity_limit' => 500,
        ];
    }
}
