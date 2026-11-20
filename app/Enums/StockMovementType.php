<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Initial = 'initial';
    case Sale = 'sale';
    case Restock = 'restock';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::Initial => 'Stok Awal',
            self::Sale => 'Penjualan',
            self::Restock => 'Penambahan Stok',
            self::Adjustment => 'Penyesuaian Stok',
        };
    }
}
