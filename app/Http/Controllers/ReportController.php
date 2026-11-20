<?php

namespace App\Http\Controllers;

use App\Models\OperatingExpense;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Support\Money;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        [$period, $from, $to] = $this->resolvePeriod($request);
        $report = $this->buildReport($from, $to);

        return view('reports.sales', [
            'report' => $report,
            'period' => $period,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function profitLoss(Request $request): View
    {
        [$period, $from, $to] = $this->resolvePeriod($request);
        $report = $this->buildReport($from, $to);

        return view('reports.profit-loss', [
            'report' => $report,
            'period' => $period,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$period, $from, $to] = $this->resolvePeriod($request);
        $report = $this->buildReport($from, $to);
        $summary = $report['summary'];

        $filename = 'laporan-putra-mandiri-'.$from.'-'.$to.'.csv';

        return response()->streamDownload(function () use ($summary, $report, $from, $to, $period): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            $sanitize = function (mixed $value): mixed {
                if (is_string($value) && $value !== '' && ! is_numeric($value) && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                    return "'".$value;
                }

                return $value;
            };

            $write = function (array $row) use ($output, $sanitize): void {
                fputcsv($output, array_map($sanitize, $row));
            };

            $money = fn (int $cents): string => number_format($cents / 100, 2, '.', '');

            $write(['Laporan Operasional '.config('store.name')]);
            $write(['Periode', $from.' s/d '.$to, 'Jenis', $period]);
            $write([]);
            $write(['Ringkasan']);
            $write(['Jumlah transaksi', $summary['transaction_count']]);
            $write(['Penjualan sebelum diskon', $money($summary['gross_sales_cents'])]);
            $write(['Total diskon', $money($summary['discount_cents'])]);
            $write(['Penerimaan penjualan (total transaksi)', $money($summary['net_sales_cents'])]);
            $write(['Pendapatan diakui', $money($summary['recognized_revenue_cents'])]);
            $write(['HPP (harga modal snapshot)', $money($summary['cogs_cents'])]);
            $write(['Laba kotor (diakui)', $money($summary['gross_profit_cents'])]);
            $write(['Pengeluaran operasional', $money($summary['expenses_cents'])]);
            $write(['Laba bersih sederhana', $money($summary['net_profit_cents'])]);
            $write(['Pembayaran layanan digital belum selesai', $money($summary['pending_digital_cents'])]);
            $write(['Jumlah layanan digital belum selesai', $summary['pending_digital_count']]);
            $write([]);

            $write(['Rekap Metode Pembayaran']);
            $write(['Metode', 'Jumlah transaksi', 'Total']);

            foreach ($report['payment_methods'] as $row) {
                $write([$row->payment_method->label(), $row->transaction_count, $money(Money::toCents($row->total))]);
            }

            $write([]);
            $write(['Rekap Kasir']);
            $write(['Kasir', 'Jumlah transaksi', 'Total']);

            foreach ($report['cashiers'] as $row) {
                $write([$row->cashier_name, $row->transaction_count, $money(Money::toCents($row->total))]);
            }

            $write([]);
            $write(['Barang Terlaris']);
            $write(['Produk', 'SKU', 'Jumlah terjual', 'Total penjualan']);

            foreach ($report['top_products'] as $row) {
                $write([$row->product_name, $row->sku, $row->total_quantity, $money(Money::toCents($row->total_revenue))]);
            }

            $write([]);
            $write(['Pengeluaran per Kategori']);
            $write(['Kategori', 'Total']);

            foreach ($report['expense_categories'] as $row) {
                $write([$row->category, $money(Money::toCents($row->total))]);
            }

            $write([]);
            $write(['Penjualan Harian']);
            $write(['Tanggal', 'Jumlah transaksi', 'Total']);

            foreach ($report['daily'] as $row) {
                $write([$row['date'], $row['transaction_count'], $money($row['total_cents'])]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function resolvePeriod(Request $request): array
    {
        $request->validate([
            'period' => ['nullable', 'in:daily,monthly,custom'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $period = $request->string('period', 'daily')->toString();
        $today = now()->toDateString();

        if ($period === 'monthly') {
            $from = now()->startOfMonth()->toDateString();
            $to = $today;
        } elseif ($period === 'custom') {
            $from = $request->date('date_from')?->toDateString() ?? $today;
            $to = $request->date('date_to')?->toDateString() ?? $today;
        } else {
            $period = 'daily';
            $from = $today;
            $to = $today;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$period, $from, $to];
    }

    /**
     * @return array{
     *     summary: array<string, int>,
     *     payment_methods: Collection<int, object>,
     *     cashiers: Collection<int, object>,
     *     top_products: Collection<int, object>,
     *     daily: array<int, array{date: string, transaction_count: int, total_cents: int}>,
     *     expense_categories: Collection<int, object>
     * }
     */
    private function buildReport(string $from, string $to): array
    {
        $salesAggregate = Sale::query()
            ->betweenDates($from, $to)
            ->selectRaw('COUNT(*) as transaction_count, COALESCE(SUM(subtotal), 0) as gross_sales, COALESCE(SUM(discount_amount), 0) as total_discount, COALESCE(SUM(total), 0) as net_sales')
            ->first();

        $recognized = $this->recognizedItemsQuery($from, $to)
            ->selectRaw('COALESCE(SUM(total), 0) as revenue, COALESCE(SUM(cost_price * quantity), 0) as cogs')
            ->first();

        $pendingDigital = DB::table('digital_service_details')
            ->join('sale_items', 'sale_items.id', '=', 'digital_service_details.sale_item_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereIn('digital_service_details.status', ['pending', 'failed'])
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(sale_items.total), 0) as total')
            ->first();

        $expensesCents = Money::toCents(
            OperatingExpense::query()
                ->betweenDates($from, $to)
                ->sum('amount'),
        );

        $grossSalesCents = Money::toCents($salesAggregate->gross_sales);
        $discountCents = Money::toCents($salesAggregate->total_discount);
        $netSalesCents = Money::toCents($salesAggregate->net_sales);
        $recognizedRevenueCents = Money::toCents($recognized->revenue);
        $cogsCents = Money::toCents($recognized->cogs);
        $grossProfitCents = $recognizedRevenueCents - $cogsCents;

        $summary = [
            'transaction_count' => (int) $salesAggregate->transaction_count,
            'gross_sales_cents' => $grossSalesCents,
            'discount_cents' => $discountCents,
            'net_sales_cents' => $netSalesCents,
            'recognized_revenue_cents' => $recognizedRevenueCents,
            'cogs_cents' => $cogsCents,
            'gross_profit_cents' => $grossProfitCents,
            'expenses_cents' => $expensesCents,
            'net_profit_cents' => $grossProfitCents - $expensesCents,
            'pending_digital_cents' => Money::toCents($pendingDigital->total),
            'pending_digital_count' => (int) $pendingDigital->total_count,
        ];

        $paymentMethods = Sale::query()
            ->betweenDates($from, $to)
            ->select('payment_method', DB::raw('COUNT(*) as transaction_count'), DB::raw('COALESCE(SUM(total), 0) as total'))
            ->groupBy('payment_method')
            ->orderBy('payment_method')
            ->get();

        $cashiers = DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->groupBy('users.id', 'users.name')
            ->select('users.name as cashier_name', DB::raw('COUNT(*) as transaction_count'), DB::raw('COALESCE(SUM(sales.total), 0) as total'))
            ->orderByDesc('total')
            ->get();

        $topProducts = $this->recognizedItemsQuery($from, $to)
            ->select('product_name', 'sku', DB::raw('SUM(quantity) as total_quantity'), DB::raw('COALESCE(SUM(total), 0) as total_revenue'))
            ->groupBy('product_name', 'sku')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        $expenseCategories = DB::table('operating_expenses')
            ->whereDate('expense_date', '>=', $from)
            ->whereDate('expense_date', '<=', $to)
            ->groupBy('category')
            ->select('category', DB::raw('COALESCE(SUM(amount), 0) as total'))
            ->orderByDesc('total')
            ->get();

        return [
            'summary' => $summary,
            'payment_methods' => $paymentMethods,
            'cashiers' => $cashiers,
            'top_products' => $topProducts,
            'daily' => $this->dailySeries($from, $to),
            'expense_categories' => $expenseCategories,
        ];
    }

    private function recognizedItemsQuery(string $from, string $to): Builder
    {
        return SaleItem::query()
            ->whereHas('sale', fn (Builder $query) => $query->betweenDates($from, $to))
            ->where(function (Builder $query): void {
                $query->where('product_type', 'physical')
                    ->orWhereHas('digitalDetail', fn (Builder $digital) => $digital->where('status', 'completed'));
            });
    }

    /**
     * @return array<int, array{date: string, transaction_count: int, total_cents: int}>
     */
    private function dailySeries(string $from, string $to): array
    {
        $rows = Sale::query()
            ->betweenDates($from, $to)
            ->selectRaw('DATE(created_at) as sale_date, COUNT(*) as transaction_count, COALESCE(SUM(total), 0) as total')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $series = [];

        foreach (CarbonPeriod::create($from, $to) as $date) {
            $row = $rows->get($date->toDateString());

            $series[] = [
                'date' => $date->toDateString(),
                'transaction_count' => (int) ($row->transaction_count ?? 0),
                'total_cents' => Money::toCents($row->total ?? 0),
            ];
        }

        return $series;
    }
}
