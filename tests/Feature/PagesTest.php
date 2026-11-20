<?php

use App\Models\Category;
use App\Models\OperatingExpense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;

test('halaman master data dan stok dapat dirender owner', function () {
    $owner = pmOwner();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'stock' => 5,
        'min_stock' => 1,
    ]);
    StockMovement::factory()->create(['product_id' => $product->id, 'user_id' => $owner->id]);

    $this->actingAs($owner)->get(route('dashboard'))->assertOk();
    $this->actingAs($owner)->get(route('categories.edit', $category))->assertOk();
    $this->actingAs($owner)->get(route('products.edit', $product))->assertOk();
    $this->actingAs($owner)->get(route('products.stock', $product))->assertOk();
    $this->actingAs($owner)->get(route('stock-movements.index'))->assertOk();
});

test('halaman pengeluaran dan pengguna dapat dirender owner', function () {
    $owner = pmOwner();
    $expense = OperatingExpense::factory()->create(['user_id' => $owner->id]);
    $kasir = pmKasir();

    $this->actingAs($owner)->get(route('expenses.edit', $expense))->assertOk();
    $this->actingAs($owner)->get(route('users.edit', $kasir))->assertOk();
    $this->actingAs($owner)->get(route('users.edit', $owner))->assertOk();
});

test('dashboard kasir dan halaman riwayat dapat dirender dengan data', function () {
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

    $this->actingAs($kasir)->get(route('dashboard'))->assertOk();
    $this->actingAs($kasir)->get(route('sales.index', ['payment_method' => 'cash']))->assertOk();
    $this->actingAs($kasir)->get(route('sales.show', $sale))->assertOk();
    $this->actingAs($kasir)->get(route('digital.index', ['status' => 'pending']))->assertOk();

    $this->actingAs(pmOwner())->get(route('digital.index'))->assertOk();
});

test('halaman laporan dengan data dapat dirender', function () {
    $owner = pmOwner();
    $product = Product::factory()->create(['sell_price' => 5000, 'stock' => 5]);

    $this->actingAs(pmKasir())->post(route('pos.checkout'), pmCheckoutPayload($product, [
        'paid_amount' => 5000,
    ]))->assertRedirect();

    $this->actingAs($owner)->get(route('reports.sales', ['period' => 'monthly']))->assertOk();
    $this->actingAs($owner)->get(route('reports.profit-loss', ['period' => 'monthly']))->assertOk();
    $this->actingAs($owner)->get(route('reports.export'))->assertOk();
});

test('menu kasir tidak menampilkan tautan master data', function () {
    $kasir = pmKasir();

    $response = $this->actingAs($kasir)->get(route('dashboard'));

    $response->assertOk();
    $response->assertDontSee(route('products.index'), false);
    $response->assertDontSee('Laporan Penjualan');
});
