<?php

use App\Support\Money;

test('format rupiah konsisten', function () {
    expect(Money::toCents(15000))->toBe(1500000)
        ->and(Money::toCents('15000.50'))->toBe(1500050)
        ->and(Money::fromCents(1500050))->toBe('15000.50')
        ->and(Money::formatCents(1500000))->toBe('Rp 15.000')
        ->and(Money::formatCents(1500050))->toBe('Rp 15.000,50')
        ->and(Money::formatCents(-500))->toBe('-Rp 5')
        ->and(Money::percentOf(10000, 12.5))->toBe(1250);
});
