<?php

namespace App\Models;

use App\Enums\DigitalServiceStatus;
use Database\Factories\DigitalServiceDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sale_item_id',
    'service_type',
    'provider',
    'phone_number',
    'customer_id',
    'meter_number',
    'nominal',
    'status',
    'provider_reference',
    'token_number',
    'notes',
    'completed_at',
    'completed_by_id',
])]
class DigitalServiceDetail extends Model
{
    /** @use HasFactory<DigitalServiceDetailFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DigitalServiceStatus::class,
            'nominal' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', DigitalServiceStatus::Pending->value);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', DigitalServiceStatus::Completed->value);
    }

    public function isPending(): bool
    {
        return $this->status === DigitalServiceStatus::Pending;
    }
}
