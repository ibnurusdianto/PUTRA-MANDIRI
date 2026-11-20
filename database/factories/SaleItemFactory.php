<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleItem>
 */
class SaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = 1;
        $price = fake()->numberBetween(2_000, 50_000);

        return [
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'sku' => 'SKU-'.fake()->unique()->numerify('########'),
            'product_type' => ProductType::Physical,
            'quantity' => $quantity,
            'unit_price' => $price,
            'cost_price' => (int) round($price * 0.8),
            'subtotal' => $price * $quantity,
            'discount_amount' => 0,
            'total' => $price * $quantity,
        ];
    }
}
