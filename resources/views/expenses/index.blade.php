@extends('adminlte::page')

@section('title', 'Pengeluaran Operasional - ' . config('store.name'))

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h3 class="mb-0 fw-bold"><i class="bi bi-wallet2 text-success me-1"></i> Pengeluaran Operasional</h3>
            <small class="text-muted">Biaya operasional toko: listrik, air, sewa, kebersihan, gaji, dan pemeliharaan.</small>
        </div>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary px-3 py-2 fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> Catat Pengeluaran
        </a>
    </div>
@stop

@section('content')
    @include('partials.flash')
    <div class="card shadow-sm mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('expenses.index') }}" class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label for="date_from" class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                </div>
                <div class="col-6 col-md-3">
                    <label for="date_to" class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                </div>
                <div class="col-12 col-md-3">
                    <label for="category" class="form-label small fw-semibold mb-1">Kategori Pengeluaran</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $expenseCategory)
                            <option value="{{ $expenseCategory }}" @selected(($filters['category'] ?? '') === $expenseCategory)>
                                {{ $expenseCategory }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Terapkan
                    </button>
                    @if (array_filter($filters))
                        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary" title="Reset filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm border-0 bg-success-subtle border-success-subtle mb-3">
        <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span class="text-success-emphasis fw-bold">Total Pengeluaran Operasional</span>
                <div class="small text-muted">Akumulasi pengeluaran pada periode / filter yang dipilih</div>
            </div>
            <strong class="fs-4 text-success tabular-nums">{{ \App\Support\Money::format($total) }}</strong>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if ($expenses->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-wallet2 display-5 text-secondary mb-2"></i>
                    <p class="mt-2 mb-1 fw-semibold text-dark">Belum ada pengeluaran operasional yang cocok dengan filter.</p>
                    <a href="{{ route('expenses.create') }}" class="btn btn-sm btn-primary mt-2">
                        <i class="bi bi-plus-lg me-1"></i> Catat Pengeluaran Baru
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="min-width: 120px;">Tanggal</th>
                                <th scope="col">Kategori</th>
                                <th scope="col">Keterangan</th>
                                <th scope="col">Dicatat Oleh</th>
                                <th scope="col" class="text-end">Nominal Biaya</th>
                                <th scope="col" class="text-end" style="min-width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expenses as $expense)
                                <tr>
                                    <td class="text-nowrap small text-muted">{{ $expense->expense_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-body-secondary text-dark border">
                                            {{ $expense->category }}
                                        </span>
                                    </td>
                                    <td>{{ $expense->description ?: '-' }}</td>
                                    <td class="small">{{ $expense->user?->name ?? '-' }}</td>
                                    <td class="text-end fw-bold text-danger tabular-nums">
                                        {{ \App\Support\Money::format($expense->amount) }}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="table-actions">
                                            <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-outline-primary"
                                                title="Ubah data pengeluaran" aria-label="Ubah data">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Hapus catatan pengeluaran {{ $expense->category }} senilai {{ \App\Support\Money::format($expense->amount) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    title="Hapus catatan pengeluaran" aria-label="Hapus pengeluaran">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="small text-muted">
                        Menampilkan {{ $expenses->firstItem() ?? 0 }} - {{ $expenses->lastItem() ?? 0 }} dari total {{ $expenses->total() }} catatan pengeluaran
                    </div>
                    <div>
                        {{ $expenses->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
