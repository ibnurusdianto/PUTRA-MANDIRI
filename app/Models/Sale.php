<?php

namespace App\Models;

use App\Enums\DiscountType;
use App\Enums\PaymentMethod;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable([
    'invoice_number',
    'checkout_token',
    'user_id',
    'subtotal',
    'discount_type',
    'discount_value',
    'discount_amount',
    'total',
    'payment_method',
    'paid_amount',
    'change_amount',
    'notes',
])]
class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_type' => DiscountType::class,
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function digitalDetails(): HasManyThrough
    {
        return $this->hasManyThrough(DigitalServiceDetail::class, SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [
            $startDate.' 00:00:00',
            $endDate.' 23:59:59',
        ]);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function hasPendingDigitalServices(): bool
    {
        return $this->items()
            ->whereHas('digitalDetail', fn (Builder $query) => $query->where('status', 'pending'))
            ->exists();
    }
}
