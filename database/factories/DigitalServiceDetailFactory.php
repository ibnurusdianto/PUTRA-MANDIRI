<?php

namespace Database\Factories;

use App\Enums\DigitalServiceStatus;
use App\Models\DigitalServiceDetail;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DigitalServiceDetail>
 */
class DigitalServiceDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sale_item_id' => SaleItem::factory(),
            'service_type' => 'pulsa',
            'provider' => 'Telkomsel',
            'phone_number' => '08'.fake()->numerify('##########'),
            'customer_id' => null,
            'meter_number' => null,
            'nominal' => 10_000,
            'status' => DigitalServiceStatus::Pending,
            'provider_reference' => null,
            'token_number' => null,
            'notes' => null,
            'completed_at' => null,
            'completed_by_id' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DigitalServiceStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
