<?php

use App\Models\DigitalServiceDetail;
use App\Models\OperatingExpense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Support\Facades\Log;

test('security headers disertakan pada setiap respon web', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-XSS-Protection', '0');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
});

test('rute demo dan dokumentasi dinonaktifkan secara default', function () {
    $this->get('/demo/dashboard-v2')->assertNotFound();
    $this->get('/docs')->assertNotFound();
});

test('filter rentang tanggal terbalik dinormalisasi secara otomatis pada riwayat penjualan', function () {
    $owner = pmOwner();
    $product = Product::factory()->create();

    $saleOld = Sale::factory()->create([
        'user_id' => $owner->id,
        'created_at' => now()->subDays(5),
    ]);

    $this->actingAs($owner)->get(route('sales.index', [
        'date_from' => now()->toDateString(),
        'date_to' => now()->subDays(10)->toDateString(),
    ]))
        ->assertOk()
        ->assertSee($saleOld->invoice_number);
});

test('filter rentang tanggal terbalik dinormalisasi secara otomatis pada pengeluaran', function () {
    $owner = pmOwner();

    $expense = OperatingExpense::factory()->create([
        'user_id' => $owner->id,
        'expense_date' => now()->subDays(3)->toDateString(),
    ]);

    $this->actingAs($owner)->get(route('expenses.index', [
        'date_from' => now()->toDateString(),
        'date_to' => now()->subDays(7)->toDateString(),
    ]))
        ->assertOk()
        ->assertSee($expense->description);
});

test('filter rentang tanggal terbalik dinormalisasi secara otomatis pada mutasi stok', function () {
    $owner = pmOwner();
    $product = Product::factory()->create();

    $movement = StockMovement::factory()->create([
        'product_id' => $product->id,
        'user_id' => $owner->id,
        'created_at' => now()->subDays(2),
        'note' => 'Catatan mutasi uji balik tanggal',
    ]);

    $this->actingAs($owner)->get(route('stock-movements.index', [
        'date_from' => now()->toDateString(),
        'date_to' => now()->subDays(5)->toDateString(),
    ]))
        ->assertOk()
        ->assertSee('Catatan mutasi uji balik tanggal');
});

test('security logging mencatat percobaan login yang gagal', function () {
    Log::spy();

    $this->post(route('login.store'), [
        'email' => 'unknown@putramandiri.test',
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    Log::shouldHaveReceived('warning')
        ->with('Percobaan login gagal.', Mockery::on(function ($context) {
            return isset($context['ip']) && $context['email'] === 'unknown@putramandiri.test';
        }))
        ->once();
});

test('security logging mencatat perubahan role pengguna', function () {
    Log::spy();
    $owner = pmOwner();
    $kasir = pmKasir();

    $this->actingAs($owner)->put(route('users.update', $kasir), [
        'name' => $kasir->name,
        'email' => $kasir->email,
        'role' => 'owner',
    ])->assertRedirect(route('users.index'));

    Log::shouldHaveReceived('notice')
        ->with('Data pengguna diperbarui.', Mockery::on(function ($context) use ($kasir, $owner) {
            return $context['actor_id'] === $owner->id
                && $context['user_id'] === $kasir->id
                && $context['role_before'] === 'kasir'
                && $context['role_after'] === 'owner';
        }))
        ->once();
});

test('security logging mencatat penyesuaian stok barang', function () {
    Log::spy();
    $owner = pmOwner();
    $product = Product::factory()->create(['stock' => 10]);

    app(StockService::class)->adjust($product, 15, $owner, 'Penyesuaian stok uji audit');

    Log::shouldHaveReceived('notice')
        ->with('Penyesuaian stok barang (opname).', Mockery::on(function ($context) use ($product, $owner) {
            return $context['product_id'] === $product->id
                && $context['sku'] === $product->sku
                && $context['stock_before'] === 10
                && $context['stock_after'] === 15
                && $context['diff'] === 5
                && $context['reason'] === 'Penyesuaian stok uji audit'
                && $context['user_id'] === $owner->id;
        }))
        ->once();
});

test('policy digital service aman saat relasi sale tidak tersedia', function () {
    $kasir = pmKasir();
    $detail = new DigitalServiceDetail;

    expect($kasir->can('view', $detail))->toBeFalse();
    expect($kasir->can('update', $detail))->toBeFalse();
});
