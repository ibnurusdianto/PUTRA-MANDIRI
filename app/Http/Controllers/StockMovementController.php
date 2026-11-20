<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'product_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'in:initial,sale,restock,adjustment'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');

        if ($dateFrom !== null && $dateTo !== null && $dateFrom->greaterThan($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $movements = StockMovement::query()
            ->with(['product:id,name,sku', 'user:id,name', 'sale:id,invoice_number'])
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->when($dateFrom !== null, fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== null, fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('stock-movements.index', [
            'movements' => $movements,
            'products' => Product::query()->orderBy('name')->get(['id', 'name', 'sku']),
            'types' => StockMovementType::cases(),
            'filters' => $request->only(['product_id', 'type', 'date_from', 'date_to']),
        ]);
    }
}
