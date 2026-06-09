<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TicketTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_type_name' => $this->faker->randomElement(['Adult', 'Child', 'Senior', 'Student']),
            'base_price'       => $this->faker->randomElement([20, 25, 15, 10]),
        ];
    }
}
