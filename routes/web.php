<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DigitalServiceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('can:use-pos')->group(function (): void {
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('pos/products', [PosController::class, 'search'])->name('pos.products');
        Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    });

    Route::middleware('can:view-sales')->group(function (): void {
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');

        Route::get('digital', [DigitalServiceController::class, 'index'])->name('digital.index');
        Route::patch('digital/{digitalService}', [DigitalServiceController::class, 'update'])->name('digital.update');
    });

    Route::middleware('can:manage-catalog')->group(function (): void {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('products', ProductController::class)->except('show');

        Route::get('products/{product}/stock', [ProductController::class, 'stock'])->name('products.stock');
        Route::post('products/{product}/stock/restock', [ProductController::class, 'restock'])->name('products.stock.restock');
        Route::post('products/{product}/stock/adjust', [ProductController::class, 'adjust'])->name('products.stock.adjust');

        Route::get('stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
    });

    Route::middleware('can:view-reports')->group(function (): void {
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    Route::middleware('can:manage-expenses')->group(function (): void {
        Route::resource('expenses', ExpenseController::class)->except('show');
    });

    Route::middleware('can:manage-users')->group(function (): void {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
    });
});
