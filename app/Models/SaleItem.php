<?php

namespace App\Models;

use App\Enums\ProductType;
use Database\Factories\SaleItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'sale_id',
    'product_id',
    'product_name',
    'sku',
    'product_type',
    'quantity',
    'unit_price',
    'cost_price',
    'subtotal',
    'discount_amount',
    'total',
])]
class SaleItem extends Model
{
    /** @use HasFactory<SaleItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_type' => ProductType::class,
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function digitalDetail(): HasOne
    {
        return $this->hasOne(DigitalServiceDetail::class);
    }
}
