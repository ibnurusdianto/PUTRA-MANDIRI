# Panduan Impor Database - PUTRA MANDIRI (POS & Toko 24 Jam)

Dokumen ini berisi petunjuk resmi impor skema database MySQL / MariaDB untuk aplikasi **PUTRA MANDIRI** melalui phpMyAdmin maupun antarmuka baris perintah (CLI).

---

## 1. Spesifikasi Mesin & Target Database

- **Target DBMS**: MySQL 8.x / MariaDB 10.x (Terverifikasi kompatibel dengan MariaDB 10.11 LTS bawaan XAMPP).
- **Storage Engine**: `InnoDB` (Mendukung transaksi ACID dan integritas relasi *foreign key*).
- **Karakter & Collation**: `utf8mb4` dengan collation `utf8mb4_unicode_ci`.
- **Format File**: Teks biasa UTF-8 tanpa BOM.

---

## 2. Deskripsi Berkas SQL

Pada direktori `database/` tersedia dua berkas dengan isi SQL yang **100% identik**:

1. **`database/putra_mandiri.sql`**
   - Berkas SQL utama yang **direkomendasikan** untuk diimpor melalui phpMyAdmin atau MySQL CLI.
2. **`database/putra_mandiri.db`**
   - Salinan teks SQL MySQL dengan nama berkas berekstensi `.db` sesuai kebutuhan pengguna.
   - **PENTING**: Berkas ini **BUKAN** berkas biner SQLite dan **TIDAK** menggunakan sintaks khusus SQLite. Ekstensi `.db` hanya penamaan berkas alternatif, isinya adalah sintaks standar MySQL.

Kedua berkas memiliki *checksum* SHA-256 yang identik dan tidak memuat perintah destruktif seperti `DROP DATABASE`, `DROP TABLE`, maupun `TRUNCATE`.

---

## 3. Langkah Impor melalui phpMyAdmin

### Langkah A: Membuat Database Kosong
1. Buka browser dan akses phpMyAdmin (biasanya di `http://localhost/phpmyadmin`).
2. Masuk ke tab **Databases** (Basis data).
3. Masukkan nama database yang direncanakan, misalnya: `putra_mandiri`.
4. Pilih Collation: `utf8mb4_unicode_ci`.
5. Klik **Create** (Buat).

*(Opsional via SQL Console di phpMyAdmin)*:
```sql
CREATE DATABASE `putra_mandiri` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Langkah B: Memilih Database
- Pada panel daftar database di sisi kiri phpMyAdmin, **klik nama database** yang baru saja dibuat (`putra_mandiri`) agar menjadi database aktif.
- Pastikan di bagian atas phpMyAdmin tertera: `Server: ... » Database: putra_mandiri`.

### Langkah C: Melakukan Impor Berkas SQL
1. Klik tab **Import** (Impor) di menu atas.
2. Pada bagian **File to import** (Berkas yang diimpor), klik **Choose File** (Pilih Berkas).
3. Pilih berkas `putra_mandiri.sql` yang berada di dalam folder `database/` proyek ini.
4. Pastikan opsi **Format** tertera sebagai **SQL**.
5. Biarkan pengaturan lainnya pada nilai default.
6. Gulir ke bagian paling bawah dan klik tombol **Import** (Kirim / Go).
7. Tunggu beberapa detik hingga phpMyAdmin menampilkan pesan sukses berwarna hijau:
   > *"Import has been successfully finished, ... queries executed."*

---

## 4. Verifikasi Struktur Tabel dan Data Awal

Setelah impor selesai, periksa database `putra_mandiri`:

1. **16 Tabel Terbentuk Sempurna**:
   - `users` (Pengguna & Hak Akses: Owner dan Kasir)
   - `password_reset_tokens` (Reset token autentikasi)
   - `sessions` (Penyimpanan session web)
   - `cache` & `cache_locks` (Infrastruktur cache aplikasi)
   - `jobs`, `job_batches`, `failed_jobs` (Infrastruktur antrean background job)
   - `migrations` (Riwayat migrasi Laravel)
   - `categories` (Kategori produk dan layanan)
   - `products` (Master barang fisik, pulsa, dan token)
   - `operating_expenses` (Pencatatan beban/pengeluaran toko)
   - `sales` (Header transaksi penjualan POS)
   - `sale_items` (Rincian barang/layanan per transaksi)
   - `stock_movements` (Audit mutasi dan penyesuaian stok)
   - `digital_service_details` (Pencatatan layanan pulsa & token manual)

2. **Verifikasi Kategori Awal**:
   Buka tabel `categories`. Pastikan 6 kategori berikut sudah tersedia:
   - Sembako
   - Mie & Kopi
   - Rokok
   - Gas & Galon
   - Pulsa & Token
   - Snack & Minuman

3. **Verifikasi Tabel Migrations**:
   Buka tabel `migrations`. Pastikan 12 baris migrasi dasar telah terdaftar pada `batch` = 1. Hal ini menjaga agar perintah Laravel `php artisan migrate` tetap sinkron dan tidak menimbulkan bentrok saat dijalankan di masa mendatang.

---

## 5. Penyesuaian Koneksi Aplikasi Laravel

Buka berkas `.env` pada root direktori aplikasi (jangan gunakan kredensial sensitif di berkas publik):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=putra_mandiri
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

> **Catatan**: Jika menggunakan XAMPP standar lokal, biasanya `DB_USERNAME=root` dan `DB_PASSWORD=` (dikosongkan).

---

## 6. Pembuatan Akun Awal (Owner & Kasir)

Demi alasan keamanan siber:
- Berkas SQL **tidak menyertakan data akun pengguna**, karena kata sandi tidak boleh disimpan dalam teks polos (*plaintext*) dan tidak boleh menggunakan *hash* palsu/sembarangan.
- Pembuatan akun dilakukan secara resmi melalui seeder bawaan proyek yang mengamankan enkripsi sandi (*Bcrypt hash*).

### Langkah Pembuatan Akun:
Buka terminal/PowerShell pada direktori proyek, lalu jalankan:
```bash
php artisan db:seed --class=UserSeeder
```

Seeder akan secara otomatis membuat 2 akun:
1. **Owner**: `owner@putramandiri.test`
2. **Kasir**: `hardiansah@putramandiri.test` (Muhammad Hardiansah)

*Tips Password*:
Jika Anda ingin menentukan password akun sebelum seeding, Anda dapat menambahkan variabel berikut pada berkas `.env`:
```env
INITIAL_OWNER_PASSWORD=PasswordRahasiaOwner123!
INITIAL_CASHIER_PASSWORD=PasswordRahasiaKasir123!
```
Jika variabel tersebut tidak diisi, seeder akan secara otomatis men-*generate* password acak 16 karakter dan menampilkannya di terminal. Simpan password tersebut untuk login pertama kali.

---

## 7. Status Sinkronisasi Migrasi Laravel

Berkas SQL ini telah memuat data pada tabel `migrations` yang mencakup 12 berkas migrasi aktif:
- `0001_01_01_000000_create_users_table`
- `0001_01_01_000001_create_cache_table`
- `0001_01_01_000002_create_jobs_table`
- `2026_09_30_000000_redact_secrets_in_adminlte_activity_log`
- `2026_11_19_181148_add_role_to_users_table`
- `2026_11_19_181149_create_categories_table`
- `2026_11_19_181150_create_products_table`
- `2026_11_19_181151_create_sales_table`
- `2026_11_19_181155_create_sale_items_table`
- `2026_11_19_181156_create_stock_movements_table`
- `2026_11_19_181157_create_digital_service_details_table`
- `2026_11_19_181158_create_operating_expenses_table`

Ketika Anda memeriksa status migrasi dengan:
```bash
php artisan migrate:status
```
Semua migrasi akan berstatus `[1] Ran`. Laravel tidak akan mencoba membuat ulang tabel-tabel ini.

---

## 8. Peringatan Keamanan & Batasan Penggunaan

1. **Hanya untuk Database Kosong**: Berkas ini ditujukan untuk instalasi baru pada database kosong. Jangan mengimpor berkas ini ke database yang sudah memiliki tabel transaksi atau data operasional aktif tanpa melakukan backup penuh terlebih dahulu.
2. **Integritas Transaksi**: Skema database menggunakan tipe data `DECIMAL(15,2)` untuk seluruh nilai moneter, kolom snapshot produk pada detail transaksi (`sale_items`), dan *foreign key* `RESTRICT` pada relasi penting untuk mencegah data historis terhapus secara tidak sengaja.
3. **Hasil Validasi**:
   - Seluruh pernyataan DDL disusun menggunakan grammar resmi MySQL Laravel (`Illuminate\Database\Schema\Grammars\MySqlGrammar`).
   - Urutan pembuatan tabel diatur secara topologis berdasarkan ketergantungan relasi kunci asing (10 relasi *foreign key*).
   - Telah melalui validasi statis dan pemeriksaan relasi bebas dari *circular dependency*.
   - Karena server MySQL lokal sedang dalam status *offline/stopped* pada lingkungan kerja saat ini, impor fisik langsung ke instance server MySQL aktif belum dijalankan. Selalu uji impor pada database pengujian sebelum menerapkannya di lingkungan produksi.
