<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cost = fake()->numberBetween(2_000, 50_000);

        return [
            'category_id' => Category::factory(),
            'sku' => 'SKU-'.Str::upper(Str::random(8)),
            'barcode' => null,
            'name' => Str::title(fake()->unique()->words(3, true)),
            'type' => ProductType::Physical,
            'unit' => 'pcs',
            'provider' => null,
            'nominal' => null,
            'cost_price' => $cost,
            'sell_price' => $cost + fake()->numberBetween(500, 5_000),
            'stock' => 50,
            'min_stock' => 5,
            'is_active' => true,
        ];
    }

    public function pulsa(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProductType::Pulsa,
            'provider' => 'Telkomsel',
            'nominal' => 10_000,
            'cost_price' => 10_500,
            'sell_price' => 12_000,
            'stock' => 0,
            'min_stock' => 0,
        ]);
    }

    public function token(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ProductType::Token,
            'provider' => 'PLN',
            'nominal' => 20_000,
            'cost_price' => 20_500,
            'sell_price' => 22_000,
            'stock' => 0,
            'min_stock' => 0,
        ]);
    }

    public function lowStock(int $stock = 2): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $stock,
            'min_stock' => 5,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
