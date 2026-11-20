<?php

use App\Models\Product;
use App\Models\Sale;

test('struk menampilkan data transaksi dan dapat dicetak ulang tanpa mengubah data', function () {
    $kasir = pmKasir();
    $product = Product::factory()->create([
        'name' => 'Beras Premium 5 kg',
        'sku' => 'BERAS-5KG',
        'sell_price' => 68000,
        'stock' => 5,
    ]);

    $this->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($product, [
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'paid_amount' => 70000,
    ]))->assertRedirect();

    $sale = Sale::query()->firstOrFail();
    $updatedAt = $sale->updated_at;

    $firstPrint = $this->actingAs($kasir)->get(route('sales.receipt', $sale));
    $secondPrint = $this->actingAs($kasir)->get(route('sales.receipt', $sale));

    $firstPrint->assertOk()
        ->assertSee(config('store.name'))
        ->assertSee($sale->invoice_number)
        ->assertSee($kasir->name)
        ->assertSee('Beras Premium 5 kg');

    $secondPrint->assertOk();

    expect($sale->fresh()->updated_at->equalTo($updatedAt))->toBeTrue();
    $this->assertDatabaseCount('sales', 1);
    $this->assertDatabaseCount('sale_items', 1);
    $this->assertDatabaseCount('stock_movements', 1);
});

test('struk token yang selesai menampilkan nomor token', function () {
    $kasir = pmKasir();
    $tokenProduct = Product::factory()->token()->create();

    $this->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($tokenProduct, [
        'items' => [[
            'product_id' => $tokenProduct->id,
            'quantity' => 1,
            'meter_number' => '123456789',
        ]],
        'paid_amount' => 22000,
    ]))->assertRedirect();

    $sale = Sale::query()->firstOrFail();
    $detail = $sale->items()->firstOrFail()->digitalDetail;

    $this->actingAs($kasir)->patch(route('digital.update', $detail), [
        'status' => 'completed',
        'token_number' => 'TOKEN-ABC-123',
    ])->assertRedirect();

    $this->actingAs($kasir)->get(route('sales.receipt', $sale))
        ->assertOk()
        ->assertSee('TOKEN-ABC-123')
        ->assertSee('Selesai');
});
