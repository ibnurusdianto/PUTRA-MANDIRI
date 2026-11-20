<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\Money;
use Illuminate\Support\Str;

test('checkout berhasil menyimpan transaksi dan mengurangi stok', function () {
    $kasir = pmKasir();
    $product = Product::factory()->create([
        'cost_price' => 3000,
        'sell_price' => 5000,
        'stock' => 10,
    ]);

    $payload = pmCheckoutPayload($product, [
        'items' => [['product_id' => $product->id, 'quantity' => 2]],
        'paid_amount' => 20000,
    ]);

    $response = $this->actingAs($kasir)->post(route('pos.checkout'), $payload);

    $sale = Sale::query()->firstOrFail();
    $response->assertRedirect(route('sales.show', $sale));

    expect(Money::toCents($sale->subtotal))->toBe(1000000)
        ->and(Money::toCents($sale->total))->toBe(1000000)
        ->and(Money::toCents($sale->paid_amount))->toBe(2000000)
        ->and(Money::toCents($sale->change_amount))->toBe(1000000)
        ->and($sale->user_id)->toBe($kasir->id)
        ->and($product->fresh()->stock)->toBe(8);

    $item = $sale->items()->firstOrFail();
    expect($item->quantity)->toBe(2)
        ->and(Money::toCents($item->unit_price))->toBe(500000)
        ->and(Money::toCents($item->cost_price))->toBe(300000)
        ->and(Money::toCents($item->total))->toBe(1000000);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'sale_id' => $sale->id,
        'type' => 'sale',
        'quantity' => -2,
        'stock_before' => 10,
        'stock_after' => 8,
    ]);
});

test('baris barang yang sama digabung dalam satu detail transaksi', function () {
    $kasir = pmKasir();
    $product = Product::factory()->create(['sell_price' => 4000, 'stock' => 10]);

    $this->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($product, [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
            ['product_id' => $product->id, 'quantity' => 2],
        ],
        'paid_amount' => 12000,
    ]));

    $sale = Sale::query()->firstOrFail();
    expect($sale->items()->count())->toBe(1);
    expect($sale->items()->firstOrFail()->quantity)->toBe(3);
    expect($product->fresh()->stock)->toBe(7);
});

test('stok tidak cukup menolak checkout tanpa perubahan parsial', function () {
    $kasir = pmKasir();
    $product = Product::factory()->create(['sell_price' => 5000, 'stock' => 3]);

    $response = $this->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($product, [
        'items' => [['product_id' => $product->id, 'quantity' => 4]],
        'paid_amount' => 20000,
    ]));

    $response->assertSessionHasErrors('items');
    $this->assertDatabaseCount('sales', 0);
    $this->assertDatabaseCount('sale_items', 0);
    $this->assertDatabaseCount('stock_movements', 0);
    expect($product->fresh()->stock)->toBe(3);
});

test('harga dan total dari browser tidak dipercaya', function () {
    $kasir = pmKasir();
    $product = Product::factory()->create(['cost_price' => 2000, 'sell_price' => 5000, 'stock' => 5]);

    $payload = pmCheckoutPayload($product, [
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 1,
            'cost_price' => 1,
            'subtotal' => 1,
            'total' => 1,
        ]],
        'paid_amount' => 5000,
        'subtotal' => 1,
        'total' => 1,
        'discount_amount' => 99999,
    ]);

    $this->actingAs($kasir)->post(route('pos.checkout'), $payload)->assertRedirect();

    $sale = Sale::query()->firstOrFail();
    expect(Money::toCents($sale->subtotal))->toBe(500000)
        ->and(Money::toCents($sale->total))->toBe(500000)
        ->and(Money::toCents($sale->discount_amount))->toBe(0)
        ->and(Money::toCents($sale->items()->firstOrFail()->unit_price))->toBe(500000);
});

test('pengiriman ulang checkout dengan token yang sama tidak membuat transaksi ganda', function () {
    $kasir = pmKasir();
    $product = Product::factory()->create(['sell_price' => 5000, 'stock' => 10]);
    $token = (string) Str::uuid();

    $payload = pmCheckoutPayload($product, [
        'checkout_token' => $token,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'paid_amount' => 5000,
    ]);

    $first = $this->actingAs($kasir)->post(route('pos.checkout'), $payload);
    $second = $this->actingAs($kasir)->post(route('pos.checkout'), $payload);

    $this->assertDatabaseCount('sales', 1);
    $this->assertDatabaseCount('sale_items', 1);
    $this->assertDatabaseCount('stock_movements', 1);
    expect($product->fresh()->stock)->toBe(9);

    $sale = Sale::query()->firstOrFail();
    $first->assertRedirect(route('sales.show', $sale));
    $second->assertRedirect(route('sales.show', $sale));
});

test('token checkout milik kasir lain tidak dapat dipakai ulang', function () {
    $kasirPertama = pmKasir();
    $kasirKedua = pmKasir();
    $product = Product::factory()->create(['sell_price' => 5000, 'stock' => 5]);
    $token = (string) Str::uuid();

    $payload = pmCheckoutPayload($product, [
        'checkout_token' => $token,
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'paid_amount' => 5000,
    ]);

    $this->actingAs($kasirPertama)->post(route('pos.checkout'), $payload)->assertRedirect();

    $response = $this->actingAs($kasirKedua)->post(route('pos.checkout'), $payload);

    $response->assertSessionHasErrors(['checkout_token' => 'Token checkout tidak valid.']);
    $this->assertDatabaseCount('sales', 1);
    expect($product->fresh()->stock)->toBe(4);
});

test('kegagalan menyimpan detail membatalkan transaksi dan mutasi stok', function () {
    $kasir = pmKasir();
    $product = Product::factory()->create(['sell_price' => 5000, 'stock' => 5]);

    SaleItem::creating(function (): void {
        throw new RuntimeException('simulasi gagal simpan detail');
    });

    try {
        $this->withoutExceptionHandling()->actingAs($kasir)->post(
            route('pos.checkout'),
            pmCheckoutPayload($product, [
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
                'paid_amount' => 5000,
            ]),
        );

        $this->fail('Exception simulasi seharusnya dilempar.');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('simulasi gagal simpan detail');
    } finally {
        SaleItem::flushEventListeners();
    }

    $this->assertDatabaseCount('sales', 0);
    $this->assertDatabaseCount('sale_items', 0);
    $this->assertDatabaseCount('stock_movements', 0);
    expect($product->fresh()->stock)->toBe(5);
});
