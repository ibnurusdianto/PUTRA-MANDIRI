<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

function pmOwner(array $attributes = []): User
{
    return User::factory()->owner()->create($attributes);
}

function pmKasir(array $attributes = []): User
{
    return User::factory()->kasir()->create($attributes);
}

/**
 * Payload checkout default; harga tidak dikirim karena server selalu menghitung ulang.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function pmCheckoutPayload(Product $product, array $overrides = []): array
{
    return array_merge([
        'checkout_token' => (string) Str::uuid(),
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
        'payment_method' => 'cash',
        'paid_amount' => (float) $product->sell_price * 10,
    ], $overrides);
}
