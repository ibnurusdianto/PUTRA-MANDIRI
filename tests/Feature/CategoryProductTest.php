<?php

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;

test('owner dapat membuat, mengubah, dan menghapus kategori', function () {
    $owner = pmOwner();

    $this->actingAs($owner)->post(route('categories.store'), [
        'name' => 'Kebutuhan Rumah',
        'description' => 'Sabun dan pembersih',
        'is_active' => '1',
    ])->assertRedirect(route('categories.index'));

    $category = Category::query()->where('name', 'Kebutuhan Rumah')->firstOrFail();
    expect($category->is_active)->toBeTrue();

    $this->actingAs($owner)->put(route('categories.update', $category), [
        'name' => 'Kebutuhan Rumah Tangga',
        'description' => 'Sabun dan pembersih',
        'is_active' => '1',
    ])->assertRedirect(route('categories.index'));

    expect($category->fresh()->name)->toBe('Kebutuhan Rumah Tangga');

    $this->actingAs($owner)->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('nama kategori duplikat ditolak', function () {
    $owner = pmOwner();
    Category::factory()->create(['name' => 'Sembako']);

    $response = $this->actingAs($owner)->post(route('categories.store'), [
        'name' => 'Sembako',
        'is_active' => '1',
    ]);

    $response->assertSessionHasErrors(['name' => 'Nama kategori sudah digunakan.']);
});

test('kasir tidak dapat mengubah kategori', function () {
    $kasir = pmKasir();

    $this->actingAs($kasir)->post(route('categories.store'), [
        'name' => 'Kategori Kasir',
        'is_active' => '1',
    ])->assertForbidden();
});

test('owner dapat membuat barang fisik', function () {
    $owner = pmOwner();
    $category = Category::factory()->create();

    $response = $this->actingAs($owner)->post(route('products.store'), [
        'category_id' => $category->id,
        'sku' => 'skm-001',
        'barcode' => '8991234567890',
        'name' => 'Susu Kental Manis',
        'type' => 'physical',
        'unit' => 'kaleng',
        'cost_price' => 9000,
        'sell_price' => 11000,
        'stock' => 12,
        'min_stock' => 3,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('products.index'));

    $product = Product::query()->where('sku', 'SKM-001')->firstOrFail();
    expect($product->stock)->toBe(12)
        ->and($product->is_active)->toBeTrue();

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => StockMovementType::Initial->value,
        'quantity' => 12,
        'stock_before' => 0,
        'stock_after' => 12,
    ]);
});

test('SKU dan barcode duplikat ditolak', function () {
    $owner = pmOwner();
    $category = Category::factory()->create();
    Product::factory()->create(['sku' => 'SKU-UNIK', 'barcode' => '1234567890', 'category_id' => $category->id]);

    $basePayload = [
        'category_id' => $category->id,
        'name' => 'Produk Baru',
        'type' => 'physical',
        'unit' => 'pcs',
        'cost_price' => 1000,
        'sell_price' => 2000,
        'stock' => 5,
        'min_stock' => 1,
        'is_active' => '1',
    ];

    $this->actingAs($owner)->post(route('products.store'), [
        ...$basePayload,
        'sku' => 'SKU-UNIK',
        'barcode' => '9999999999',
    ])->assertSessionHasErrors(['sku' => 'Kode barang (SKU) sudah digunakan.']);

    $this->actingAs($owner)->post(route('products.store'), [
        ...$basePayload,
        'sku' => 'SKU-BARU',
        'barcode' => '1234567890',
    ])->assertSessionHasErrors(['barcode' => 'Barcode sudah digunakan produk lain.']);
});

test('layanan digital wajib memiliki provider dan nominal', function () {
    $owner = pmOwner();
    $category = Category::factory()->create();

    $response = $this->actingAs($owner)->post(route('products.store'), [
        'category_id' => $category->id,
        'sku' => 'PULSA-10K',
        'name' => 'Pulsa 10 Ribu',
        'type' => 'pulsa',
        'unit' => 'transaksi',
        'cost_price' => 10500,
        'sell_price' => 11500,
        'stock' => 0,
        'min_stock' => 0,
        'is_active' => '1',
    ]);

    $response->assertSessionHasErrors(['provider', 'nominal']);
});

test('kasir tidak dapat menambah barang', function () {
    $kasir = pmKasir();
    $category = Category::factory()->create();

    $this->actingAs($kasir)->post(route('products.store'), [
        'category_id' => $category->id,
        'sku' => 'SKU-KASIR',
        'name' => 'Barang Kasir',
        'type' => 'physical',
        'unit' => 'pcs',
        'cost_price' => 1000,
        'sell_price' => 2000,
        'stock' => 1,
        'min_stock' => 1,
        'is_active' => '1',
    ])->assertForbidden();
});

test('kategori yang masih digunakan tidak dapat dihapus', function () {
    $owner = pmOwner();
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    $response = $this->actingAs($owner)->delete(route('categories.destroy', $category));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('barang dengan riwayat dinonaktifkan bukan dihapus', function () {
    $owner = pmOwner();
    $product = Product::factory()->create();
    StockMovement::factory()->create(['product_id' => $product->id]);

    $this->actingAs($owner)->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);
});

test('barang tanpa riwayat dapat dihapus', function () {
    $owner = pmOwner();
    $product = Product::factory()->create();

    $this->actingAs($owner)->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('owner dapat menambah dan menyesuaikan stok dengan riwayat mutasi', function () {
    $owner = pmOwner();
    $product = Product::factory()->create(['stock' => 5, 'min_stock' => 2]);

    $this->actingAs($owner)->post(route('products.stock.restock', $product), [
        'quantity' => 3,
        'note' => 'Pembelian supplier',
    ])->assertRedirect(route('products.stock', $product));

    expect($product->fresh()->stock)->toBe(8);
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => StockMovementType::Restock->value,
        'quantity' => 3,
        'stock_before' => 5,
        'stock_after' => 8,
    ]);

    $this->actingAs($owner)->post(route('products.stock.adjust', $product), [
        'stock' => 7,
        'reason' => 'Selisih opname',
    ])->assertRedirect(route('products.stock', $product));

    expect($product->fresh()->stock)->toBe(7);
    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => StockMovementType::Adjustment->value,
        'quantity' => -1,
        'stock_before' => 8,
        'stock_after' => 7,
        'note' => 'Selisih opname',
    ]);
});

test('penyesuaian stok wajib memiliki alasan dan tidak boleh negatif', function () {
    $owner = pmOwner();
    $product = Product::factory()->create(['stock' => 5]);

    $this->actingAs($owner)->post(route('products.stock.adjust', $product), [
        'stock' => 4,
    ])->assertSessionHasErrors(['reason']);

    $this->actingAs($owner)->post(route('products.stock.adjust', $product), [
        'stock' => -1,
        'reason' => 'Salah input',
    ])->assertSessionHasErrors(['stock']);

    expect($product->fresh()->stock)->toBe(5);
});
