<?php

use App\Models\Product;
use App\Models\Sale;

test('kasir tidak dapat mengakses endpoint khusus owner', function (string $routeName) {
    $kasir = pmKasir();

    $this->actingAs($kasir)->get(route($routeName))->assertForbidden();
})->with([
    'categories.index',
    'categories.create',
    'products.index',
    'products.create',
    'stock-movements.index',
    'reports.sales',
    'reports.profit-loss',
    'reports.export',
    'expenses.index',
    'expenses.create',
    'users.index',
    'users.create',
]);

test('kasir dapat mengakses kasir, riwayat, dan layanan digital', function () {
    $kasir = pmKasir();

    $this->actingAs($kasir)->get(route('dashboard'))->assertOk();
    $this->actingAs($kasir)->get(route('pos.index'))->assertOk();
    $this->actingAs($kasir)->get(route('sales.index'))->assertOk();
    $this->actingAs($kasir)->get(route('digital.index'))->assertOk();
});

test('owner dapat mengakses seluruh halaman utama', function (string $routeName) {
    $owner = pmOwner();

    $this->actingAs($owner)->get(route($routeName))->assertOk();
})->with([
    'dashboard',
    'pos.index',
    'categories.index',
    'categories.create',
    'products.index',
    'products.create',
    'stock-movements.index',
    'sales.index',
    'digital.index',
    'reports.sales',
    'reports.profit-loss',
    'expenses.index',
    'expenses.create',
    'users.index',
    'users.create',
]);

test('kasir hanya dapat melihat transaksi miliknya', function () {
    $kasir = pmKasir();
    $lain = pmKasir();
    $milikSendiri = Sale::factory()->create(['user_id' => $kasir->id]);
    $milikOrangLain = Sale::factory()->create(['user_id' => $lain->id]);

    $this->actingAs($kasir)->get(route('sales.show', $milikSendiri))->assertOk();
    $this->actingAs($kasir)->get(route('sales.show', $milikOrangLain))->assertForbidden();
    $this->actingAs($kasir)->get(route('sales.receipt', $milikOrangLain))->assertForbidden();
});

test('owner dapat melihat transaksi seluruh kasir', function () {
    $owner = pmOwner();
    $sale = Sale::factory()->create(['user_id' => pmKasir()->id]);

    $this->actingAs($owner)->get(route('sales.show', $sale))->assertOk();
    $this->actingAs($owner)->get(route('sales.receipt', $sale))->assertOk();
});

test('kasir tidak dapat mengelola pengguna melalui URL langsung', function () {
    $kasir = pmKasir();
    $owner = pmOwner();

    $this->actingAs($kasir)->post(route('users.store'), [
        'name' => 'Penyusup',
        'email' => 'penyusup@example.test',
        'role' => 'owner',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertForbidden();

    $this->assertDatabaseMissing('users', ['email' => 'penyusup@example.test']);
    $this->assertFalse($owner->fresh()->isKasir());
});

test('produk tidak aktif tidak dapat dijual', function () {
    $kasir = pmKasir();
    $product = Product::factory()->inactive()->create(['sell_price' => 5000, 'stock' => 5]);

    $response = $this->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($product, [
        'items' => [['product_id' => $product->id, 'quantity' => 1]],
        'paid_amount' => 5000,
    ]));

    $response->assertSessionHasErrors('items');
    $this->assertDatabaseCount('sales', 0);
    expect($product->fresh()->stock)->toBe(5);
});
