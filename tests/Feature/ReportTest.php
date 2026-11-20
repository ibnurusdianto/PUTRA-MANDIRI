<?php

use App\Models\OperatingExpense;
use App\Models\Product;
use App\Models\Sale;
use App\Support\Money;
use Carbon\Carbon;

function pmSellPhysical(Product $product, int $quantity = 1, array $overrides = []): Sale
{
    test()->actingAs(pmKasir())->post(route('pos.checkout'), pmCheckoutPayload($product, [
        'items' => [['product_id' => $product->id, 'quantity' => $quantity]],
        'paid_amount' => (float) $product->sell_price * $quantity,
        ...$overrides,
    ]))->assertRedirect();

    return Sale::query()->latest('id')->firstOrFail();
}

test('laporan memakai harga modal snapshot meski harga produk berubah', function () {
    $owner = pmOwner();
    $product = Product::factory()->create([
        'cost_price' => 3000,
        'sell_price' => 5000,
        'stock' => 10,
    ]);

    pmSellPhysical($product, 2);

    $product->update(['cost_price' => 9000, 'sell_price' => 12000]);

    $response = $this->actingAs($owner)->get(route('reports.sales'));

    $response->assertOk();
    $response->assertViewHas('report', function (array $report): bool {
        return $report['summary']['recognized_revenue_cents'] === 1000000
            && $report['summary']['cogs_cents'] === 600000
            && $report['summary']['gross_profit_cents'] === 400000;
    });
});

test('penjualan diskon hpp dan laba konsisten dengan transaksi sumber', function () {
    $owner = pmOwner();
    $product = Product::factory()->create([
        'cost_price' => 3000,
        'sell_price' => 5000,
        'stock' => 10,
    ]);

    $sale = pmSellPhysical($product, 2, [
        'discount_type' => 'nominal',
        'discount_value' => 1000,
        'paid_amount' => 9000,
    ]);

    expect(Money::toCents($sale->total))->toBe(900000);

    OperatingExpense::factory()->create([
        'user_id' => $owner->id,
        'expense_date' => now()->toDateString(),
        'amount' => 1500,
    ]);

    $response = $this->actingAs($owner)->get(route('reports.profit-loss'));

    $response->assertOk();
    $response->assertViewHas('report', function (array $report): bool {
        $summary = $report['summary'];

        return $summary['gross_sales_cents'] === 1000000
            && $summary['discount_cents'] === 100000
            && $summary['net_sales_cents'] === 900000
            && $summary['recognized_revenue_cents'] === 900000
            && $summary['cogs_cents'] === 600000
            && $summary['gross_profit_cents'] === 300000
            && $summary['expenses_cents'] === 150000
            && $summary['net_profit_cents'] === 150000;
    });
});

test('filter tanggal laporan inklusif sampai akhir hari', function () {
    $owner = pmOwner();
    $product = Product::factory()->create([
        'cost_price' => 3000,
        'sell_price' => 5000,
        'stock' => 10,
    ]);

    $this->travelTo(Carbon::parse('2026-01-15 23:59:30'));
    pmSellPhysical($product);

    $this->travelTo(Carbon::parse('2026-01-16 00:00:10'));
    pmSellPhysical($product);

    $this->travelTo(Carbon::parse('2026-01-16 08:00:00'));

    $this->actingAs($owner)
        ->get(route('reports.sales', [
            'period' => 'custom',
            'date_from' => '2026-01-15',
            'date_to' => '2026-01-15',
        ]))
        ->assertOk()
        ->assertViewHas('report', fn (array $report): bool => $report['summary']['transaction_count'] === 1
            && $report['summary']['net_sales_cents'] === 500000);

    $this->actingAs($owner)
        ->get(route('reports.sales', [
            'period' => 'custom',
            'date_from' => '2026-01-15',
            'date_to' => '2026-01-16',
        ]))
        ->assertOk()
        ->assertViewHas('report', fn (array $report): bool => $report['summary']['transaction_count'] === 2);

    $this->travelBack();
});

test('ekspor CSV laporan dapat diunduh owner', function () {
    $owner = pmOwner();
    $product = Product::factory()->create(['sell_price' => 5000, 'stock' => 5]);
    pmSellPhysical($product);

    $response = $this->actingAs($owner)->get(route('reports.export'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});
