<?php

namespace Database\Factories;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $before = fake()->numberBetween(10, 100);
        $quantity = fake()->numberBetween(1, 10);

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'sale_id' => null,
            'type' => StockMovementType::Restock,
            'quantity' => $quantity,
            'stock_before' => $before,
            'stock_after' => $before + $quantity,
            'note' => null,
        ];
    }
}
