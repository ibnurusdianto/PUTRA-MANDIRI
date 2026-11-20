@php($exportUrl = route('reports.export', ['period' => $period, 'date_from' => $from, 'date_to' => $to]))

<div class="card shadow-sm mb-3 no-print">
    <div class="card-body p-3">
        <form method="GET" action="{{ $action }}" class="row g-2 align-items-end" id="report-filter-form">
            <div class="col-12 col-md-3">
                <label for="period" class="form-label small fw-semibold mb-1">
                    <i class="bi bi-calendar-range text-success me-1"></i> Pilihan Periode
                </label>
                <select name="period" id="period" class="form-select">
                    <option value="daily" @selected($period === 'daily')>Hari Ini (Harian)</option>
                    <option value="monthly" @selected($period === 'monthly')>Bulan Ini (Berjalan)</option>
                    <option value="custom" @selected($period === 'custom')>Rentang Tanggal Khusus</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="date_from" class="form-label small fw-semibold mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" id="date_from" value="{{ $from }}" class="form-control">
            </div>
            <div class="col-6 col-md-3">
                <label for="date_to" class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" id="date_to" value="{{ $to }}" class="form-control">
            </div>
            <div class="col-12 col-md-3 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Terapkan
                </button>
                <a href="{{ $exportUrl }}" class="btn btn-outline-success fw-semibold" title="Unduh CSV">
                    <i class="bi bi-filetype-csv me-1"></i> Ekspor
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()" title="Cetak Laporan">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
        </form>

        <div class="small text-muted mt-2 pt-2 border-top d-flex flex-wrap justify-content-between align-items-center gap-1">
            <div>
                <i class="bi bi-clock-history me-1"></i>
                Periode aktif: <strong class="text-dark">{{ \Carbon\Carbon::parse($from)->format('d F Y') }}</strong>
                s/d <strong class="text-dark">{{ \Carbon\Carbon::parse($to)->format('d F Y') }}</strong>
                <span class="text-muted">(Zona Waktu Asia/Jakarta)</span>
            </div>
            <div class="d-none d-md-block text-muted">
                Semua nilai nominal dihitung dalam Rupiah (Rp)
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        (() => {
            const period = document.getElementById('period');
            const from = document.getElementById('date_from');
            const to = document.getElementById('date_to');
            const form = document.getElementById('report-filter-form');
            if (!period || !from || !to) return;

            const pad = (n) => String(n).padStart(2, '0');
            const formatDate = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

            period.addEventListener('change', () => {
                const now = new Date();
                if (period.value === 'daily') {
                    from.value = formatDate(now);
                    to.value = formatDate(now);
                    form?.submit();
                } else if (period.value === 'monthly') {
                    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                    const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    from.value = formatDate(firstDay);
                    to.value = formatDate(lastDay);
                    form?.submit();
                }
            });

            [from, to].forEach((input) => input.addEventListener('change', () => {
                period.value = 'custom';
            }));
        })();
    </script>
@endpush
