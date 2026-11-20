<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function restock(Product $product, int $quantity, User $user, ?string $note = null): StockMovement
    {
        $this->ensurePhysicalProduct($product);

        if ($quantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah penambahan stok harus minimal 1.',
            ]);
        }

        return DB::transaction(function () use ($product, $quantity, $user, $note): StockMovement {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $stockBefore = $lockedProduct->stock;
            $stockAfter = $stockBefore + $quantity;

            $lockedProduct->stock = $stockAfter;
            $lockedProduct->save();

            Log::info('Penambahan stok barang.', [
                'product_id' => $lockedProduct->id,
                'sku' => $lockedProduct->sku,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'user_id' => $user->id,
            ]);

            return $lockedProduct->stockMovements()->create([
                'user_id' => $user->id,
                'type' => StockMovementType::Restock->value,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'note' => $note,
            ]);
        });
    }

    public function adjust(Product $product, int $newStock, User $user, string $reason): ?StockMovement
    {
        $this->ensurePhysicalProduct($product);

        if ($newStock < 0) {
            throw ValidationException::withMessages([
                'stock' => 'Stok tidak boleh negatif.',
            ]);
        }

        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'Alasan penyesuaian stok wajib diisi.',
            ]);
        }

        return DB::transaction(function () use ($product, $newStock, $user, $reason): ?StockMovement {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);
            $stockBefore = $lockedProduct->stock;

            if ($stockBefore === $newStock) {
                return null;
            }

            $lockedProduct->stock = $newStock;
            $lockedProduct->save();

            Log::notice('Penyesuaian stok barang (opname).', [
                'product_id' => $lockedProduct->id,
                'sku' => $lockedProduct->sku,
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'diff' => $newStock - $stockBefore,
                'reason' => $reason,
                'user_id' => $user->id,
            ]);

            return $lockedProduct->stockMovements()->create([
                'user_id' => $user->id,
                'type' => StockMovementType::Adjustment->value,
                'quantity' => $newStock - $stockBefore,
                'stock_before' => $stockBefore,
                'stock_after' => $newStock,
                'note' => $reason,
            ]);
        });
    }

    private function ensurePhysicalProduct(Product $product): void
    {
        if (! $product->type->isPhysical()) {
            throw ValidationException::withMessages([
                'product' => 'Produk layanan digital tidak memiliki stok fisik.',
            ]);
        }
    }
}
