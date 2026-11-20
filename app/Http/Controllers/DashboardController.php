<?php

namespace App\Http\Controllers;

use App\Models\DigitalServiceDetail;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isOwner = $user->isOwner();
        $startOfDay = now()->startOfDay();
        $endOfDay = now()->endOfDay();
        $chartStart = now()->subDays(6)->startOfDay();

        $chartRows = Sale::query()
            ->when(! $isOwner, fn ($query) => $query->where('user_id', $user->id))
            ->whereBetween('created_at', [$chartStart, $endOfDay])
            ->selectRaw('DATE(created_at) as sale_date, COUNT(*) as transaction_count, COALESCE(SUM(total), 0) as total')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $chart = [];

        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo);
            $row = $chartRows->get($date->toDateString());

            $chart[] = [
                'label' => $date->translatedFormat('d M'),
                'total_cents' => Money::toCents($row->total ?? 0),
                'transaction_count' => (int) ($row->transaction_count ?? 0),
            ];
        }

        $todayQuery = Sale::query()
            ->when(! $isOwner, fn ($query) => $query->where('user_id', $user->id))
            ->whereBetween('created_at', [$startOfDay, $endOfDay]);

        $todayAggregate = (clone $todayQuery)
            ->selectRaw('COUNT(*) as transaction_count, COALESCE(SUM(total), 0) as total')
            ->first();

        $recentSales = (clone $todayQuery)
            ->with('user:id,name')
            ->latest()
            ->limit(8)
            ->get();

        $lowStockProducts = Product::query()
            ->physical()
            ->active()
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->limit(10)
            ->get(['id', 'name', 'sku', 'stock', 'min_stock', 'unit']);

        $stats = [
            'transaction_count' => (int) $todayAggregate->transaction_count,
            'sales_total_cents' => Money::toCents($todayAggregate->total),
            'active_products' => Product::query()->active()->count(),
            'low_stock_count' => Product::query()->physical()->active()->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock')->count(),
            'out_of_stock_count' => Product::query()->physical()->active()->where('stock', '<=', 0)->count(),
        ];

        $grossProfitCents = null;
        $recognizedRevenueCents = null;
        $pendingDigitalCount = 0;
        $pendingDigitalServices = collect();
        $bestSellers = collect();

        if ($isOwner) {
            $recognized = SaleItem::query()
                ->whereHas('sale', fn ($query) => $query->whereBetween('created_at', [$startOfDay, $endOfDay]))
                ->where(function ($query): void {
                    $query->where('product_type', 'physical')
                        ->orWhereHas('digitalDetail', fn ($digital) => $digital->where('status', 'completed'));
                })
                ->selectRaw('COALESCE(SUM(total), 0) as revenue, COALESCE(SUM(cost_price * quantity), 0) as cogs')
                ->first();

            $recognizedRevenueCents = Money::toCents($recognized->revenue);
            $grossProfitCents = $recognizedRevenueCents - Money::toCents($recognized->cogs);

            $pendingDigitalCount = DigitalServiceDetail::query()->pending()->count();

            $pendingDigitalServices = DigitalServiceDetail::query()
                ->pending()
                ->with(['saleItem:id,product_name,sale_id', 'saleItem.sale:id,invoice_number,user_id,created_at', 'saleItem.sale.user:id,name'])
                ->latest()
                ->limit(5)
                ->get();

            $bestSellers = SaleItem::query()
                ->where('product_type', 'physical')
                ->whereHas('sale', fn ($query) => $query->whereBetween('created_at', [$chartStart, $endOfDay]))
                ->selectRaw('product_name, SUM(quantity) as total_quantity, SUM(total) as total_revenue')
                ->groupBy('product_name')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get();
        }

        return view('dashboard', [
            'isOwner' => $isOwner,
            'chart' => $chart,
            'chartMaxCents' => max(1, collect($chart)->max('total_cents')),
            'stats' => $stats,
            'recentSales' => $recentSales,
            'lowStockProducts' => $lowStockProducts,
            'recognizedRevenueCents' => $recognizedRevenueCents,
            'grossProfitCents' => $grossProfitCents,
            'pendingDigitalCount' => $pendingDigitalCount,
            'pendingDigitalServices' => $pendingDigitalServices,
            'bestSellers' => $bestSellers,
        ]);
    }
}
