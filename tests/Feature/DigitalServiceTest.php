<?php

use App\Enums\DigitalServiceStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Support\Money;

function pmCreateDigitalSale(Product $product, array $target = [], ?User $kasir = null): Sale
{
    $kasir ??= pmKasir();

    test()->actingAs($kasir)->post(route('pos.checkout'), pmCheckoutPayload($product, [
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            ...$target,
        ]],
        'paid_amount' => (float) $product->sell_price * 2,
    ]))->assertRedirect();

    return Sale::query()->latest('id')->firstOrFail();
}

test('layanan digital belum selesai tidak diakui sebagai pendapatan', function () {
    $owner = pmOwner();
    $pulsa = Product::factory()->pulsa()->create();

    $sale = pmCreateDigitalSale($pulsa, ['phone_number' => '081234567890']);
    $itemTotal = Money::toCents($sale->items()->firstOrFail()->total);

    $response = $this->actingAs($owner)->get(route('reports.sales'));

    $response->assertOk();
    $response->assertViewHas('report', function (array $report) use ($itemTotal): bool {
        return $report['summary']['recognized_revenue_cents'] === 0
            && $report['summary']['cogs_cents'] === 0
            && $report['summary']['pending_digital_cents'] === $itemTotal
            && $report['summary']['pending_digital_count'] === 1;
    });
});

test('layanan digital dapat ditandai selesai dengan referensi dan token', function () {
    $kasir = pmKasir();
    $tokenProduct = Product::factory()->token()->create();
    $sale = pmCreateDigitalSale($tokenProduct, [
        'customer_id' => '123456789',
        'meter_number' => '987654321',
    ], $kasir);

    $detail = $sale->items()->firstOrFail()->digitalDetail;
    expect($detail->status)->toBe(DigitalServiceStatus::Pending);

    $this->actingAs($kasir)->patch(route('digital.update', $detail), [
        'status' => 'completed',
        'provider_reference' => 'REF-123',
        'token_number' => '1234-5678-9012-3456',
    ])->assertRedirect();

    $detail->refresh();

    expect($detail->status)->toBe(DigitalServiceStatus::Completed)
        ->and($detail->provider_reference)->toBe('REF-123')
        ->and($detail->token_number)->toBe('1234-5678-9012-3456')
        ->and($detail->completed_by_id)->toBe($kasir->id)
        ->and($detail->completed_at)->not->toBeNull();

    $response = $this->actingAs(pmOwner())->get(route('reports.sales'));
    $itemTotal = Money::toCents($sale->items()->firstOrFail()->total);

    $response->assertViewHas('report', function (array $report) use ($itemTotal): bool {
        return $report['summary']['recognized_revenue_cents'] === $itemTotal
            && $report['summary']['pending_digital_cents'] === 0;
    });
});

test('status layanan yang sudah final tidak dapat diubah lagi', function () {
    $kasir = pmKasir();
    $pulsa = Product::factory()->pulsa()->create();
    $sale = pmCreateDigitalSale($pulsa, ['phone_number' => '081234567890'], $kasir);
    $detail = $sale->items()->firstOrFail()->digitalDetail;

    $this->actingAs($kasir)->patch(route('digital.update', $detail), [
        'status' => 'completed',
    ])->assertRedirect();

    $response = $this->actingAs($kasir)->patch(route('digital.update', $detail), [
        'status' => 'failed',
        'notes' => 'Percobaan ubah status',
    ]);

    $response->assertSessionHasErrors(['status' => 'Status layanan yang sudah final tidak dapat diubah lagi.']);
    expect($detail->fresh()->status)->toBe(DigitalServiceStatus::Completed);
});

test('layanan yang ditandai gagal wajib memiliki catatan', function () {
    $kasir = pmKasir();
    $pulsa = Product::factory()->pulsa()->create();
    $sale = pmCreateDigitalSale($pulsa, ['phone_number' => '081234567890'], $kasir);
    $detail = $sale->items()->firstOrFail()->digitalDetail;

    $this->actingAs($kasir)->patch(route('digital.update', $detail), [
        'status' => 'failed',
    ])->assertSessionHasErrors(['notes']);

    $this->actingAs($kasir)->patch(route('digital.update', $detail), [
        'status' => 'failed',
        'notes' => 'Nomor tujuan tidak valid',
    ])->assertRedirect();

    expect($detail->fresh()->status)->toBe(DigitalServiceStatus::Failed);
});

test('kasir tidak dapat mengubah layanan digital milik kasir lain', function () {
    $kasirLain = pmKasir();
    $pulsa = Product::factory()->pulsa()->create();
    $sale = pmCreateDigitalSale($pulsa, ['phone_number' => '081234567890'], $kasirLain);
    $detail = $sale->items()->firstOrFail()->digitalDetail;

    $this->actingAs(pmKasir())->patch(route('digital.update', $detail), [
        'status' => 'completed',
    ])->assertForbidden();

    expect($detail->fresh()->status)->toBe(DigitalServiceStatus::Pending);
});
