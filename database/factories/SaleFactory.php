<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10_000, 200_000);

        return [
            'invoice_number' => 'INV-TEST-'.Str::upper(Str::random(10)),
            'checkout_token' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'subtotal' => $subtotal,
            'discount_type' => null,
            'discount_value' => 0,
            'discount_amount' => 0,
            'total' => $subtotal,
            'payment_method' => PaymentMethod::Cash,
            'paid_amount' => $subtotal,
            'change_amount' => 0,
        ];
    }
}
