<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Sale;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'invoice' => ['nullable', 'string', 'max:40'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'user_id' => ['nullable', 'integer'],
            'payment_method' => ['nullable', 'in:cash,transfer,qris'],
            'digital_status' => ['nullable', 'in:pending,completed,failed'],
        ]);

        $user = $request->user();

        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');

        if ($dateFrom !== null && $dateTo !== null && $dateFrom->greaterThan($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $sales = Sale::query()
            ->with('user:id,name')
            ->withCount('items')
            ->withCount([
                'items as pending_digital_count' => fn ($query) => $query
                    ->whereHas('digitalDetail', fn ($digital) => $digital->where('status', 'pending')),
            ])
            ->when(! $user->isOwner(), fn ($query) => $query->where('user_id', $user->id))
            ->when($request->filled('invoice'), fn ($query) => $query->where('invoice_number', 'like', '%'.$request->string('invoice')->toString().'%'))
            ->when($dateFrom !== null, fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== null, fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->when($request->filled('user_id') && $user->isOwner(), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($request->filled('payment_method'), fn ($query) => $query->where('payment_method', $request->string('payment_method')->toString()))
            ->when($request->filled('digital_status'), function ($query) use ($request): void {
                $status = $request->string('digital_status')->toString();
                $query->whereHas('items.digitalDetail', fn ($digital) => $digital->where('status', $status));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
            'cashiers' => $user->isOwner()
                ? User::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'paymentMethods' => PaymentMethod::cases(),
            'filters' => $request->only(['invoice', 'date_from', 'date_to', 'user_id', 'payment_method', 'digital_status']),
        ]);
    }

    public function show(Sale $sale): View
    {
        Gate::authorize('view', $sale);

        $sale->load([
            'user:id,name',
            'items.digitalDetail.completedBy:id,name',
            'stockMovements.product:id,name,sku',
        ]);

        $recognizedRevenueCents = 0;
        $recognizedCogsCents = 0;
        $pendingDigitalCents = 0;

        foreach ($sale->items as $item) {
            $isRecognized = $item->product_type->value === 'physical'
                || $item->digitalDetail?->status?->value === 'completed';

            if ($isRecognized) {
                $recognizedRevenueCents += Money::toCents($item->total);
                $recognizedCogsCents += Money::toCents($item->cost_price) * $item->quantity;
            } else {
                $pendingDigitalCents += Money::toCents($item->total);
            }
        }

        return view('sales.show', [
            'sale' => $sale,
            'recognizedRevenueCents' => $recognizedRevenueCents,
            'recognizedCogsCents' => $recognizedCogsCents,
            'pendingDigitalCents' => $pendingDigitalCents,
        ]);
    }

    public function receipt(Sale $sale): View
    {
        Gate::authorize('printReceipt', $sale);

        $sale->load(['user:id,name', 'items.digitalDetail']);

        return view('sales.receipt', [
            'sale' => $sale,
        ]);
    }
}
