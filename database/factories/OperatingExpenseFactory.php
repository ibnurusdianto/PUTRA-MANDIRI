<?php

namespace Database\Factories;

use App\Models\OperatingExpense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperatingExpense>
 */
class OperatingExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->owner(),
            'expense_date' => now()->toDateString(),
            'category' => fake()->randomElement(['Listrik', 'Air', 'Sewa', 'Kebersihan', 'Lainnya']),
            'amount' => fake()->numberBetween(10_000, 500_000),
            'description' => fake()->sentence(),
        ];
    }
}
