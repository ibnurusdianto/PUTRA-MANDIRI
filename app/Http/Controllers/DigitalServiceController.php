<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDigitalServiceRequest;
use App\Models\DigitalServiceDetail;
use App\Services\DigitalServiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DigitalServiceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $baseQuery = DigitalServiceDetail::query()
            ->when(! $user->isOwner(), fn ($query) => $query->whereHas(
                'saleItem.sale',
                fn ($sale) => $sale->where('user_id', $user->id),
            ));

        $services = (clone $baseQuery)
            ->with(['saleItem:id,sale_id,product_name,total', 'saleItem.sale:id,invoice_number,user_id,created_at', 'saleItem.sale.user:id,name', 'completedBy:id,name'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('service_type'), fn ($query) => $query->where('service_type', $request->string('service_type')->toString()))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q')->toString().'%';
                $query->where(function ($builder) use ($term): void {
                    $builder->where('phone_number', 'like', $term)
                        ->orWhere('customer_id', 'like', $term)
                        ->orWhere('meter_number', 'like', $term)
                        ->orWhere('token_number', 'like', $term)
                        ->orWhereHas('saleItem.sale', fn ($sale) => $sale->where('invoice_number', 'like', $term));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('digital.index', [
            'services' => $services,
            'pendingCount' => (clone $baseQuery)->where('status', 'pending')->count(),
            'filters' => $request->only(['status', 'service_type', 'q']),
        ]);
    }

    public function update(UpdateDigitalServiceRequest $request, DigitalServiceDetail $digitalService, DigitalServiceService $digitalServiceService): RedirectResponse
    {
        Gate::authorize('update', $digitalService);

        $digitalServiceService->updateStatus($digitalService, $request->user(), $request->validated());

        return back()->with('success', 'Status layanan digital berhasil diperbarui.');
    }
}
