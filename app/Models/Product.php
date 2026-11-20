<?php

namespace App\Models;

use App\Enums\ProductType;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'sku',
    'barcode',
    'name',
    'type',
    'unit',
    'provider',
    'nominal',
    'cost_price',
    'sell_price',
    'stock',
    'min_stock',
    'is_active',
    'image_path',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'nominal' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePhysical(Builder $query): Builder
    {
        return $query->where('type', ProductType::Physical->value);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term): void {
            $like = '%'.$term.'%';

            $query->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhere('barcode', 'like', $like);
        });
    }

    /**
     * @return array<string, string>
     */
    public function stockStatus(): array
    {
        if ($this->type->isDigital()) {
            return ['key' => 'service', 'label' => 'Layanan Digital', 'badge' => 'text-bg-info'];
        }

        return match (true) {
            $this->stock <= 0 => ['key' => 'out', 'label' => 'Habis', 'badge' => 'text-bg-danger'],
            $this->stock <= $this->min_stock => ['key' => 'low', 'label' => 'Menipis', 'badge' => 'text-bg-warning'],
            default => ['key' => 'safe', 'label' => 'Aman', 'badge' => 'text-bg-success'],
        };
    }

    public function hasTransactionHistory(): bool
    {
        return $this->saleItems()->exists() || $this->stockMovements()->exists();
    }
}
