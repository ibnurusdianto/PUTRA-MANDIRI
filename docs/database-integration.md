# Dokumentasi Integrasi Database - PUTRA MANDIRI

Dokumen ini mencatat konfigurasi, status pemetaan, verifikasi skema, dan panduan operasional integrasi backend Laravel dengan database MySQL `putra_mandiri`.

---

## 1. Konfigurasi Koneksi Database

Aplikasi Laravel telah berhasil dihubungkan ke server MySQL lokal (XAMPP). Konfigurasi aktif pada berkas `.env` (tanpa membocorkan nilai kredensial sensitif):

- **DB_CONNECTION**: `mysql`
- **DB_HOST**: `127.0.0.1`
- **DB_PORT**: `3306`
- **DB_DATABASE**: `putra_mandiri`
- **DB_USERNAME**: `root` (kredensial lokal standar)
- **DB_PASSWORD**: *(dijaga kerahasiaannya)*
- **SESSION_DRIVER**: `database` (menggunakan tabel `sessions` pada MySQL)
- **CACHE_STORE**: `database` (menggunakan tabel `cache` dan `cache_locks` pada MySQL)
- **QUEUE_CONNECTION**: `database` (menggunakan tabel `jobs` pada MySQL)

---

## 2. Pemetaan Model Eloquent dan Tabel Database

Semua model pada folder `app/Models/` telah dipetakan secara presisi dengan 16 tabel yang terbentuk di MySQL hasil impor `putra_mandiri.sql`:

| Model Eloquent | Tabel Database | Status | Primary Key | Keterangan Relasi & Integritas |
|---|---|---|---|---|
| `User` | `users` | Sesuai | `id` (bigint unsigned) | Relasi ke `sales`, `operating_expenses`, `stock_movements`. Role: `owner`, `kasir`. |
| `Category` | `categories` | Sesuai | `id` (bigint unsigned) | Relasi `hasMany` ke `products`. Dilindungi dari penghapusan bila ada produk aktif. |
| `Product` | `products` | Sesuai | `id` (bigint unsigned) | Relasi `belongsTo` ke `categories`. Tipe: `physical`, `pulsa`, `pln_token`. Foreign key `RESTRICT`. |
| `Sale` | `sales` | Sesuai | `id` (bigint unsigned) | Relasi `belongsTo` ke `users` (kasir), `hasMany` ke `sale_items`, `stock_movements`. Invoice & token idempotensi unik. |
| `SaleItem` | `sale_items` | Sesuai | `id` (bigint unsigned) | Snapshot harga (`unit_price`, `cost_price`), subtotal, diskon, dan total. Cascade delete dari `sales`, set null dari `products`. |
| `StockMovement` | `stock_movements` | Sesuai | `id` (bigint unsigned) | Audit mutasi stok fisik: `initial`, `restock`, `sale`, `adjustment`. FK `RESTRICT` ke `products`. |
| `DigitalServiceDetail` | `digital_service_details` | Sesuai | `id` (bigint unsigned) | Detail pemenuhan pulsa & token PLN manual. Relasi 1-ke-1 ke `sale_items`. Status: `pending`, `completed`, `failed`. |
| `OperatingExpense` | `operating_expenses` | Sesuai | `id` (bigint unsigned) | Pencatatan beban operasional toko untuk laporan laba rugi. FK `RESTRICT` ke `users`. |
| *(Laravel Framework)* | `sessions` | Sesuai | `id` (varchar) | Driver session database aktif. |
| *(Laravel Framework)* | `cache`, `cache_locks` | Sesuai | `key` (varchar) | Driver cache database aktif. |
| *(Laravel Framework)* | `jobs`, `job_batches`, `failed_jobs` | Sesuai | `id` | Driver queue antrean database aktif. |
| *(Laravel Framework)* | `migrations` | Sesuai | `id` (int unsigned) | Riwayat migrasi aktif (12 baris migrasi tercatat pada batch 1). |
| *(Laravel Framework)* | `password_reset_tokens` | Sesuai | `email` (varchar) | Token reset password pengguna. |

---

## 3. Penyesuaian Kode Backend

1. **Konfigurasi Lingkungan (`.env` dan `.env.example`)**:
   - Memperbarui `DB_CONNECTION=mysql` agar Laravel menggunakan driver MySQL dan mengakses basis data `putra_mandiri`.
   - Memperbarui `.env.example` dengan placeholder standar MySQL nonrahasia.
2. **Pencegahan Deadlock pada Checkout (`app/Services/SaleCheckoutService.php`)**:
   - Menambahkan pengurutan ID produk secara ketat (`sort()->values()`) sebelum eksekusi `lockForUpdate()` dengan `orderBy('id')`.
   - Mengeliminasi potensi deadlock antar-transaksi kasir konkuren pada storage engine InnoDB MySQL saat beberapa transaksi memesan kumpulan produk yang sama dengan urutan berbeda.
3. **Format Kode**:
   - Memastikan seluruh kode PHP lolos pemeriksaan *Laravel Pint*.

---

## 4. Status Sinkronisasi Migrasi

Pemeriksaan status migrasi via `php artisan migrate:status`:
- `0001_01_01_000000_create_users_table`: **[1] Ran**
- `0001_01_01_000001_create_cache_table`: **[1] Ran**
- `0001_01_01_000002_create_jobs_table`: **[1] Ran**
- `2026_09_30_000000_redact_secrets_in_adminlte_activity_log`: **[1] Ran**
- `2026_11_19_181148_add_role_to_users_table`: **[1] Ran**
- `2026_11_19_181149_create_categories_table`: **[1] Ran**
- `2026_11_19_181150_create_products_table`: **[1] Ran**
- `2026_11_19_181151_create_sales_table`: **[1] Ran**
- `2026_11_19_181155_create_sale_items_table`: **[1] Ran**
- `2026_11_19_181156_create_stock_movements_table`: **[1] Ran**
- `2026_11_19_181157_create_digital_service_details_table`: **[1] Ran**
- `2026_11_19_181158_create_operating_expenses_table`: **[1] Ran**

Semua migrasi berada dalam status sinkron. **Jangan menjalankan `php artisan migrate:fresh` atau `db:wipe`** karena akan menghapus tabel dan skema aktif.

---

## 5. Cara Menyiapkan Akun Pengguna Awal

Tabel `users` pada basis data aktif MySQL saat ini masih bersih (0 baris) karena berkas SQL awal sengaja tidak memuat data kata sandi demi keamanan hash.

Proyek telah menyediakan seeder resmi yang aman dan idempoten: `UserSeeder`.

### Langkah Eksekusi (Setelah Persetujuan):
Jalankan perintah berikut pada terminal:
```bash
php artisan db:seed --class=UserSeeder
```

Akun yang akan dibuat:
1. **Owner**: `owner@putramandiri.test` (Role: Owner)
2. **Kasir**: `hardiansah@putramandiri.test` (Nama: Muhammad Hardiansah, Role: Kasir)

*Catatan Kata Sandi*:
- Kata sandi diambil dari variabel `.env` (`INITIAL_OWNER_PASSWORD` dan `INITIAL_CASHIER_PASSWORD`).
- Jika tidak diisi pada `.env`, seeder akan menghasilkan password acak 16 karakter dan menampilkannya sekali di layar konsol.

---

## 6. Cara Menjalankan Aplikasi

1. **Jalankan Server MySQL & Web Server (XAMPP)**:
   - Pastikan modul MySQL dan Apache pada XAMPP Control Panel aktif (berjalan di port 3306).
2. **Jalankan Aplikasi Laravel**:
   ```bash
   php artisan serve
   ```
   Akses melalui browser pada `http://127.0.0.1:8000`.
3. **Build atau Dev Aset Frontend (Jika Diperlukan)**:
   ```bash
   npm run build
   ```

---

## 7. Cara Menjalankan Pengujian Secara Aman

Pengujian aplikasi dikonfigurasi melalui berkas `phpunit.xml` yang secara otomatis menggunakan basis data SQLite *in-memory* (`:memory:`):
- Basis data MySQL aktif `putra_mandiri` **100% aman dan tidak akan tersentuh** oleh proses pengujian.

Perintah menjalankan seluruh test suite:
```bash
vendor/bin/pest
```
Hasil verifikasi test suite: **94 passed (312 assertions)**.

---

## 8. Keterbatasan Integrasi yang Ada

1. **Layanan Digital (Pulsa & Token Listrik)**:
   - Form pencatatan pulsa dan token listrik beroperasi dalam mode pencatatan operasional manual toko 24 jam.
   - Belum terintegrasi API vendor/biller pihak ketiga (seperti Digiflazz, Tripay, atau Mobilepulsa). Kasir memproses transaksi pada mesin/aplikasi provider fisik, lalu mencatatkan nomor referensi/token dan menandai status sebagai Selesai di sistem.
2. **Pembayaran Digital (QRIS & Transfer Bank)**:
   - Transaksi QRIS dan Transfer dicatat sebagai konfirmasi manual oleh kasir (tidak menggunakan webhook payment gateway otomatis). Uang kembali otomatis dihitung Rp 0.
