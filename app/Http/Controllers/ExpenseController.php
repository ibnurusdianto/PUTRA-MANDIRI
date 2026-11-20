<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\OperatingExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'category' => ['nullable', 'string', 'max:50'],
        ]);

        $dateFrom = $request->date('date_from');
        $dateTo = $request->date('date_to');

        if ($dateFrom !== null && $dateTo !== null && $dateFrom->greaterThan($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $query = OperatingExpense::query()
            ->with('user:id,name')
            ->when($dateFrom !== null, fn ($builder) => $builder->whereDate('expense_date', '>=', $dateFrom))
            ->when($dateTo !== null, fn ($builder) => $builder->whereDate('expense_date', '<=', $dateTo))
            ->when($request->filled('category'), fn ($builder) => $builder->where('category', $request->string('category')->toString()));

        $total = (clone $query)->sum('amount');

        $expenses = $query->latest('expense_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('expenses.index', [
            'expenses' => $expenses,
            'total' => $total,
            'categories' => OperatingExpense::query()->distinct()->orderBy('category')->pluck('category'),
            'filters' => $request->only(['date_from', 'date_to', 'category']),
        ]);
    }

    public function create(): View
    {
        return view('expenses.create', [
            'expense' => new OperatingExpense(['expense_date' => now()->toDateString()]),
            'categories' => OperatingExpense::query()->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        OperatingExpense::query()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Pengeluaran operasional berhasil dicatat.');
    }

    public function edit(OperatingExpense $expense): View
    {
        return view('expenses.edit', [
            'expense' => $expense,
            'categories' => OperatingExpense::query()->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function update(UpdateExpenseRequest $request, OperatingExpense $expense): RedirectResponse
    {
        $expense->update($request->validated());

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Pengeluaran operasional berhasil diperbarui.');
    }

    public function destroy(OperatingExpense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Pengeluaran operasional berhasil dihapus.');
    }
}
