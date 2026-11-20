<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\SaleCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View
    {
        return view('pos.index', [
            'products' => Product::query()
                ->active()
                ->with('category:id,name')
                ->orderBy('name')
                ->limit(24)
                ->get()
                ->map(fn (Product $product): array => $this->productPayload($product)),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'checkoutToken' => (string) Str::uuid(),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $products = Product::query()
            ->active()
            ->with('category:id,name')
            ->search($request->string('q')->toString())
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->orderBy('name')
            ->limit(30)
            ->get()
            ->map(fn (Product $product): array => $this->productPayload($product));

        return response()->json(['data' => $products]);
    }

    /**
     * @return array<string, mixed>
     */
    private function productPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'category' => $product->category?->name,
            'type' => $product->type->value,
            'type_label' => $product->type->label(),
            'unit' => $product->unit,
            'price' => (float) $product->sell_price,
            'stock' => $product->stock,
            'min_stock' => $product->min_stock,
            'provider' => $product->provider,
            'nominal' => $product->nominal !== null ? (float) $product->nominal : null,
            'stock_status' => $product->stockStatus(),
        ];
    }

    public function checkout(CheckoutRequest $request, SaleCheckoutService $checkoutService): RedirectResponse
    {
        $sale = $checkoutService->checkout(
            $request->user(),
            $request->validated(),
            $request->string('checkout_token')->toString(),
        );

        return redirect()
            ->route('sales.show', $sale)
            ->with('checkout_completed', true)
            ->with('success', $sale->wasRecentlyCreated
                ? 'Transaksi '.$sale->invoice_number.' berhasil disimpan.'
                : 'Transaksi ini sudah tersimpan sebelumnya.');
    }
}
