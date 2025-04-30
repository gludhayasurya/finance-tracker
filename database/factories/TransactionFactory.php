<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['income', 'expense']);
        $title = $type === 'income'
            ? $this->faker->randomElement(['Salary', 'Freelance', 'Bonus'])
            : $this->faker->randomElement(['Food', 'Rent', 'Utilities', 'Travel', 'Other']);

        return [
            'title' => $title,
            'amount' => $this->faker->numberBetween(500, 5000),
            'type' => $type,
            'date' => $this->faker->dateTimeBetween('-6 months'),
        ];
    }

}
