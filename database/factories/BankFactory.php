<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bank>
 */
class BankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'name' => $this->faker->randomElement(['Salary', 'Freelance', 'Bonus']),
            'address' => $this->faker->address,
            'initial_balance' => $this->faker->numberBetween(500, 5000),
        ];
    }

}
