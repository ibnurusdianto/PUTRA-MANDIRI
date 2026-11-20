<?php

use App\Enums\DigitalServiceStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Support\Money;
use Illuminate\Support\Str;

test('pembayaran cash kurang ditolak', function () {
    $kasir = pmKasir();
    $product = Product::factory()->create(['sell_price' => 5000, 'stock' => 5]);

    $response = $this->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($product, [
        'paid_amount' => 4000,
    ]));

    $response->assertSessionHasErrors('paid_amount');
    $this->assertDatabaseCount('sales', 0);
    expect($product->fresh()->stock)->toBe(5);
});

test('diskon tidak valid ditolak', function (array $overrides) {
    $kasir = pmKasir();
    $product = Product::factory()->create(['sell_price' => 5000, 'stock' => 5]);

    $response = $this->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($product, [
        ...$overrides,
        'paid_amount' => 5000,
    ]));

    $response->assertSessionHasErrors('discount_value');
    $this->assertDatabaseCount('sales', 0);
})->with([
    'persen lebih dari 100' => [['discount_type' => 'percent', 'discount_value' => 101]],
    'nominal negatif' => [['discount_type' => 'nominal', 'discount_value' => -100]],
    'nominal melebihi subtotal' => [['discount_type' => 'nominal', 'discount_value' => 6000]],
]);

test('transfer dan qris tidak menghasilkan kembalian', function (string $method) {
    $kasir = pmKasir();
    $product = Product::factory()->create(['sell_price' => 5000, 'stock' => 5]);

    $this->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($product, [
        'payment_method' => $method,
        'paid_amount' => 0,
    ]))->assertRedirect();

    $sale = Sale::query()->firstOrFail();
    expect($sale->payment_method->value)->toBe($method)
        ->and(Money::toCents($sale->paid_amount))->toBe(500000)
        ->and(Money::toCents($sale->change_amount))->toBe(0);
})->with(['transfer', 'qris']);

test('diskon dialokasikan konsisten ke detail barang', function () {
    $kasir = pmKasir();
    $produkA = Product::factory()->create(['sell_price' => 3333, 'stock' => 5]);
    $produkB = Product::factory()->create(['sell_price' => 6667, 'stock' => 5]);

    $this->actingAs($kasir)->post(route('pos.checkout'), [
        'checkout_token' => (string) Str::uuid(),
        'items' => [
            ['product_id' => $produkA->id, 'quantity' => 1],
            ['product_id' => $produkB->id, 'quantity' => 1],
        ],
        'discount_type' => 'nominal',
        'discount_value' => 1000,
        'payment_method' => 'cash',
        'paid_amount' => 9000,
    ])->assertRedirect();

    $sale = Sale::query()->firstOrFail();
    $items = $sale->items()->get();

    $sumDiscount = $items->sum(fn ($item) => Money::toCents($item->discount_amount));
    $sumTotal = $items->sum(fn ($item) => Money::toCents($item->total));

    expect($sumDiscount)->toBe(100000)
        ->and($sumTotal)->toBe(Money::toCents($sale->total))
        ->and(Money::toCents($sale->subtotal))->toBe(1000000)
        ->and(Money::toCents($sale->total))->toBe(900000);
});

test('pulsa tidak mengurangi stok fisik dan tercatat pending', function () {
    $kasir = pmKasir();
    $pulsa = Product::factory()->pulsa()->create();

    $this->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($pulsa, [
        'items' => [[
            'product_id' => $pulsa->id,
            'quantity' => 1,
            'phone_number' => '081234567890',
        ]],
        'paid_amount' => 12000,
    ]))->assertRedirect();

    $sale = Sale::query()->firstOrFail();
    $item = $sale->items()->firstOrFail();

    expect($pulsa->fresh()->stock)->toBe(0)
        ->and($item->product_type->value)->toBe('pulsa')
        ->and($item->digitalDetail)->not->toBeNull()
        ->and($item->digitalDetail->status)->toBe(DigitalServiceStatus::Pending)
        ->and($item->digitalDetail->phone_number)->toBe('081234567890');

    $this->assertDatabaseCount('stock_movements', 0);
});

test('pulsa tanpa nomor HP ditolak', function () {
    $kasir = pmKasir();
    $pulsa = Product::factory()->pulsa()->create();

    $response = $this->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($pulsa, [
        'items' => [['product_id' => $pulsa->id, 'quantity' => 1]],
        'paid_amount' => 12000,
    ]));

    $response->assertSessionHasErrors('items');
    $this->assertDatabaseCount('sales', 0);
});

test('token listrik wajib memiliki ID pelanggan atau nomor meter', function () {
    $kasir = pmKasir();
    $token = Product::factory()->token()->create();

    $response = $this->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($token, [
        'items' => [['product_id' => $token->id, 'quantity' => 1]],
        'paid_amount' => 22000,
    ]));

    $response->assertSessionHasErrors('items');

    $this->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($token, [
        'items' => [[
            'product_id' => $token->id,
            'quantity' => 1,
            'customer_id' => '123456789',
            'meter_number' => '987654321',
        ]],
        'paid_amount' => 22000,
    ]))->assertRedirect();

    $this->assertDatabaseCount('sales', 1);
});
