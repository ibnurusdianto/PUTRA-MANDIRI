<?php

namespace App\Http\Controllers;

use App\Enums\ProductType;
use App\Enums\StockMovementType;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\RestockProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $products = Product::query()
            ->with('category:id,name')
            ->search($request->string('q')->toString())
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($status === 'low', fn ($query) => $query->physical()->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock'))
            ->when($status === 'out', fn ($query) => $query->physical()->where('stock', '<=', 0))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'types' => ProductType::cases(),
            'filters' => $request->only(['q', 'category_id', 'type', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'product' => new Product([
                'type' => ProductType::Physical,
                'unit' => 'pcs',
                'is_active' => true,
                'stock' => 0,
                'min_stock' => 5,
            ]),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'types' => ProductType::cases(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $data['stock'] = (int) ($data['stock'] ?? 0);

        $product = DB::transaction(function () use ($data, $request): Product {
            $product = Product::query()->create($data);

            if ($product->type->isPhysical() && $product->stock > 0) {
                $product->stockMovements()->create([
                    'user_id' => $request->user()->id,
                    'type' => StockMovementType::Initial->value,
                    'quantity' => $product->stock,
                    'stock_before' => 0,
                    'stock_after' => $product->stock,
                    'note' => 'Stok awal saat produk dibuat',
                ]);
            }

            return $product;
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Barang '.$product->name.' berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'types' => ProductType::cases(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image'], $data['stock']);

        $newType = ProductType::from($data['type']);

        if ($newType !== $product->type) {
            if ($product->hasTransactionHistory() || $product->stock !== 0) {
                return back()->withInput()->with('error', 'Jenis produk tidak dapat diubah karena produk sudah memiliki riwayat stok atau transaksi.');
            }
        }

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Barang '.$product->name.' berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->hasTransactionHistory()) {
            $product->update(['is_active' => false]);

            return redirect()
                ->route('products.index')
                ->with('success', 'Barang memiliki riwayat transaksi sehingga dinonaktifkan, bukan dihapus.');
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Barang berhasil dihapus.');
    }

    public function stock(Product $product): View
    {
        abort_unless($product->type->isPhysical(), 404);

        $product->load('category:id,name');

        $movements = $product->stockMovements()
            ->with('user:id,name')
            ->latest()
            ->limit(15)
            ->get();

        return view('products.stock', [
            'product' => $product,
            'movements' => $movements,
        ]);
    }

    public function restock(RestockProductRequest $request, Product $product, StockService $stockService): RedirectResponse
    {
        try {
            $stockService->restock($product, $request->integer('quantity'), $request->user(), $request->input('note'));
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        return redirect()
            ->route('products.stock', $product)
            ->with('success', 'Stok '.$product->name.' berhasil ditambahkan.');
    }

    public function adjust(AdjustStockRequest $request, Product $product, StockService $stockService): RedirectResponse
    {
        try {
            $movement = $stockService->adjust($product, $request->integer('stock'), $request->user(), $request->string('reason')->toString());
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        }

        if ($movement === null) {
            return redirect()
                ->route('products.stock', $product)
                ->with('success', 'Stok tidak berubah, tidak ada mutasi yang dicatat.');
        }

        return redirect()
            ->route('products.stock', $product)
            ->with('success', 'Stok '.$product->name.' berhasil disesuaikan.');
    }
}
