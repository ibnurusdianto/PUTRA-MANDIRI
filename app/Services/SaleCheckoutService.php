<?php

namespace App\Services;

use App\Enums\DigitalServiceStatus;
use App\Enums\DiscountType;
use App\Enums\PaymentMethod;
use App\Enums\ProductType;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleCheckoutService
{
    /**
     * @param  array{items: array<int, array<string, mixed>>, discount_type?: ?string, discount_value?: int|float|string|null, payment_method: string, paid_amount?: int|float|string|null, notes?: ?string}  $data
     */
    public function checkout(User $cashier, array $data, string $checkoutToken): Sale
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                return DB::transaction(function () use ($cashier, $data, $checkoutToken): Sale {
                    $existing = Sale::query()
                        ->where('checkout_token', $checkoutToken)
                        ->first();

                    if ($existing !== null) {
                        if ($existing->user_id !== $cashier->id) {
                            throw ValidationException::withMessages([
                                'checkout_token' => 'Token checkout tidak valid.',
                            ]);
                        }

                        return $existing->load(['items.digitalDetail', 'user']);
                    }

                    return $this->createSale($cashier, $data, $checkoutToken);
                });
            } catch (QueryException $exception) {
                Log::warning('Percobaan checkout menemui query exception.', [
                    'checkout_token' => $checkoutToken,
                    'cashier_id' => $cashier->id,
                    'attempt' => $attempt,
                    'error' => $exception->getMessage(),
                ]);

                if ($attempt === 3 || ! $this->isUniqueConstraintViolation($exception)) {
                    throw $exception;
                }
            }
        }

        throw new \RuntimeException('Checkout gagal diproses.');
    }

    /**
     * @param  array{items: array<int, array<string, mixed>>, discount_type?: ?string, discount_value?: int|float|string|null, payment_method: string, paid_amount?: int|float|string|null, notes?: ?string}  $data
     */
    private function createSale(User $cashier, array $data, string $checkoutToken): Sale
    {
        $items = array_values($data['items']);

        if ($items === []) {
            throw ValidationException::withMessages(['items' => 'Keranjang tidak boleh kosong.']);
        }

        $productIds = collect($items)
            ->pluck('product_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->sort()
            ->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $lines = $this->buildLines($items, $products);

        $subtotalCents = array_sum(array_column($lines, 'gross_cents'));
        $discountCents = $this->calculateDiscount($data, $subtotalCents);
        $totalCents = $subtotalCents - $discountCents;

        [$paymentMethod, $paidCents, $changeCents] = $this->calculatePayment($data, $totalCents);

        $lineDiscounts = $this->allocateDiscount(
            array_column($lines, 'gross_cents'),
            $discountCents,
        );

        $sale = Sale::query()->create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'checkout_token' => $checkoutToken,
            'user_id' => $cashier->id,
            'subtotal' => Money::fromCents($subtotalCents),
            'discount_type' => $data['discount_type'] ?? null,
            'discount_value' => $data['discount_value'] ?? 0,
            'discount_amount' => Money::fromCents($discountCents),
            'total' => Money::fromCents($totalCents),
            'payment_method' => $paymentMethod->value,
            'paid_amount' => Money::fromCents($paidCents),
            'change_amount' => Money::fromCents($changeCents),
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($lines as $index => $line) {
            /** @var Product $product */
            $product = $line['product'];
            $lineTotalCents = $line['gross_cents'] - $lineDiscounts[$index];

            $saleItem = $sale->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'product_type' => $product->type->value,
                'quantity' => $line['quantity'],
                'unit_price' => Money::fromCents($line['unit_cents']),
                'cost_price' => Money::fromCents($line['cost_cents']),
                'subtotal' => Money::fromCents($line['gross_cents']),
                'discount_amount' => Money::fromCents($lineDiscounts[$index]),
                'total' => Money::fromCents($lineTotalCents),
            ]);

            if ($product->type->isPhysical()) {
                $this->applyStockSale($sale, $saleItem, $product, $line['quantity'], $cashier);
            } else {
                $this->createDigitalDetail($saleItem, $product, $line);
            }
        }

        Log::info('Transaksi penjualan berhasil dibuat.', [
            'sale_id' => $sale->id,
            'invoice_number' => $sale->invoice_number,
            'total' => Money::formatCents($totalCents),
            'payment_method' => $paymentMethod->value,
            'cashier_id' => $cashier->id,
        ]);

        return $sale->load(['items.digitalDetail', 'user']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  Collection<int, Product>  $products
     * @return array<int, array{product: Product, quantity: int, unit_cents: int, cost_cents: int, gross_cents: int, phone_number: ?string, customer_id: ?string, meter_number: ?string}>
     */
    private function buildLines(array $items, $products): array
    {
        $lines = [];
        $physicalIndexes = [];

        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            /** @var Product|null $product */
            $product = $products->get($productId);

            if ($product === null) {
                throw ValidationException::withMessages(['items' => 'Produk pada keranjang tidak ditemukan.']);
            }

            if (! $product->is_active) {
                throw ValidationException::withMessages([
                    'items' => "Produk {$product->name} sedang tidak aktif dan tidak dapat dijual.",
                ]);
            }

            $quantity = (int) ($item['quantity'] ?? 0);

            if ($quantity < 1) {
                throw ValidationException::withMessages([
                    'items' => "Jumlah untuk {$product->name} harus minimal 1.",
                ]);
            }

            $unitCents = Money::toCents($product->sell_price);

            if ($unitCents <= 0) {
                throw ValidationException::withMessages([
                    'items' => "Harga jual {$product->name} belum diatur.",
                ]);
            }

            $costCents = Money::toCents($product->cost_price);

            if ($product->type->isPhysical()) {
                if (isset($physicalIndexes[$product->id])) {
                    $lines[$physicalIndexes[$product->id]]['quantity'] += $quantity;
                    $lines[$physicalIndexes[$product->id]]['gross_cents'] += $unitCents * $quantity;

                    continue;
                }

                $physicalIndexes[$product->id] = count($lines);
                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_cents' => $unitCents,
                    'cost_cents' => $costCents,
                    'gross_cents' => $unitCents * $quantity,
                    'phone_number' => null,
                    'customer_id' => null,
                    'meter_number' => null,
                ];

                continue;
            }

            if ($quantity !== 1) {
                throw ValidationException::withMessages([
                    'items' => "Jumlah layanan digital {$product->name} harus 1 per transaksi.",
                ]);
            }

            $phoneNumber = $this->cleanString($item['phone_number'] ?? null);
            $customerId = $this->cleanString($item['customer_id'] ?? null);
            $meterNumber = $this->cleanString($item['meter_number'] ?? null);

            if ($product->type === ProductType::Pulsa && $phoneNumber === null) {
                throw ValidationException::withMessages([
                    'items' => "Nomor HP wajib diisi untuk layanan pulsa {$product->name}.",
                ]);
            }

            if ($product->type === ProductType::Token && $customerId === null && $meterNumber === null) {
                throw ValidationException::withMessages([
                    'items' => "ID pelanggan atau nomor meter wajib diisi untuk token listrik {$product->name}.",
                ]);
            }

            $lines[] = [
                'product' => $product,
                'quantity' => 1,
                'unit_cents' => $unitCents,
                'cost_cents' => $costCents,
                'gross_cents' => $unitCents,
                'phone_number' => $phoneNumber,
                'customer_id' => $customerId,
                'meter_number' => $meterNumber,
            ];
        }

        foreach ($lines as $line) {
            /** @var Product $product */
            $product = $line['product'];

            if ($product->type->isPhysical() && $product->stock < $line['quantity']) {
                throw ValidationException::withMessages([
                    'items' => "Stok {$product->name} tidak mencukupi. Tersedia {$product->stock}, diminta {$line['quantity']}.",
                ]);
            }
        }

        return $lines;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function calculateDiscount(array $data, int $subtotalCents): int
    {
        $type = $data['discount_type'] ?? null;
        $value = $data['discount_value'] ?? 0;

        if ($type === null || $type === '' || (float) $value === 0.0) {
            return 0;
        }

        if ($type === DiscountType::Nominal->value) {
            $discount = Money::toCents($value);

            if ($discount < 0) {
                throw ValidationException::withMessages(['discount_value' => 'Diskon tidak boleh negatif.']);
            }

            if ($discount > $subtotalCents) {
                throw ValidationException::withMessages(['discount_value' => 'Diskon tidak boleh melebihi subtotal.']);
            }

            return $discount;
        }

        if ($type === DiscountType::Percent->value) {
            $percent = (float) $value;

            if ($percent < 0 || $percent > 100) {
                throw ValidationException::withMessages(['discount_value' => 'Persentase diskon harus antara 0 sampai 100.']);
            }

            return min($subtotalCents, Money::percentOf($subtotalCents, $percent));
        }

        throw ValidationException::withMessages(['discount_type' => 'Jenis diskon tidak valid.']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: PaymentMethod, 1: int, 2: int}
     */
    private function calculatePayment(array $data, int $totalCents): array
    {
        $method = PaymentMethod::tryFrom((string) ($data['payment_method'] ?? ''));

        if ($method === null) {
            throw ValidationException::withMessages(['payment_method' => 'Metode pembayaran tidak valid.']);
        }

        if ($method->isCash()) {
            $paidCents = Money::toCents($data['paid_amount'] ?? 0);

            if ($paidCents < $totalCents) {
                throw ValidationException::withMessages(['paid_amount' => 'Uang tunai yang diterima kurang dari total tagihan.']);
            }

            return [$method, $paidCents, $paidCents - $totalCents];
        }

        return [$method, $totalCents, 0];
    }

    /**
     * @param  array<int, int>  $lineGrossCents
     * @return array<int, int>
     */
    private function allocateDiscount(array $lineGrossCents, int $discountCents): array
    {
        $result = array_fill(0, count($lineGrossCents), 0);

        if ($discountCents === 0) {
            return $result;
        }

        $totalCents = array_sum($lineGrossCents);
        $allocated = 0;

        foreach ($lineGrossCents as $index => $grossCents) {
            $share = $totalCents > 0 ? intdiv($discountCents * $grossCents, $totalCents) : 0;
            $share = min($share, $grossCents);
            $result[$index] = $share;
            $allocated += $share;
        }

        $remainder = $discountCents - $allocated;

        for ($index = count($lineGrossCents) - 1; $index >= 0 && $remainder > 0; $index--) {
            $capacity = $lineGrossCents[$index] - $result[$index];
            $additional = min($capacity, $remainder);
            $result[$index] += $additional;
            $remainder -= $additional;
        }

        return $result;
    }

    private function applyStockSale(Sale $sale, SaleItem $saleItem, Product $product, int $quantity, User $cashier): void
    {
        $stockBefore = $product->stock;
        $stockAfter = $stockBefore - $quantity;

        if ($stockAfter < 0) {
            throw ValidationException::withMessages([
                'items' => "Stok {$product->name} tidak mencukupi.",
            ]);
        }

        $product->stock = $stockAfter;
        $product->save();

        $sale->stockMovements()->create([
            'product_id' => $product->id,
            'user_id' => $cashier->id,
            'sale_id' => $sale->id,
            'type' => StockMovementType::Sale->value,
            'quantity' => -$quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'note' => 'Penjualan '.$sale->invoice_number,
        ]);
    }

    /**
     * @param  array{product: Product, quantity: int, unit_cents: int, cost_cents: int, gross_cents: int, phone_number: ?string, customer_id: ?string, meter_number: ?string}  $line
     */
    private function createDigitalDetail(SaleItem $saleItem, Product $product, array $line): void
    {
        $nominalCents = $product->nominal !== null
            ? Money::toCents($product->nominal)
            : $line['unit_cents'];

        $saleItem->digitalDetail()->create([
            'service_type' => $product->type->value,
            'provider' => $product->provider,
            'phone_number' => $line['phone_number'],
            'customer_id' => $line['customer_id'],
            'meter_number' => $line['meter_number'],
            'nominal' => Money::fromCents($nominalCents),
            'status' => DigitalServiceStatus::Pending->value,
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        return 'PM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }

    private function cleanString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        return $exception->getCode() === '23000'
            || str_contains(strtolower($exception->getMessage()), 'unique')
            || str_contains(strtolower($exception->getMessage()), 'duplicate');
    }
}
