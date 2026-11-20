# PUTRA MANDIRI

Aplikasi pengelolaan toko kebutuhan sehari-hari yang beroperasi 24 jam (mengadopsi konsep ketangguhan operasional warung harian dengan tata kelola modern standar minimarket). Sistem ini dirancang untuk pencatatan transaksi kasir (POS), pengelolaan inventaris barang fisik, pencatatan layanan digital (pulsa dan token listrik) secara manual, pemantauan mutasi stok, pengendalian pengeluaran, serta penyajian laporan penjualan dan laba rugi sederhana yang transparan.

---

## 1. Identitas & Konteks Toko

- **Nama Toko / Usaha**: PUTRA MANDIRI
- **Konsep Operasional**: Toko kelontong / minimarket 24 jam melayani sembako, minuman, makanan ringan, gas LPG, air galon, pulsa telepon seluler, dan token listrik prabayar PLN.
- **Tujuan Sistem**:
  - Mempercepat proses transaksi kasir dengan antarmuka POS yang responsif pada desktop maupun perangkat bergerak.
  - Mencegah kehilangan barang melalui pencatatan mutasi stok otomatis dan audit opname.
  - Memisahkan secara tegas hak akses finansial strategis antara pemilik (Owner) dan kasir bertugas.
  - Memastikan pencatatan layanan digital tidak bercampur dengan pendapatan barang fisik sebelum layanan benar-benar terselesaikan.

---

## 2. Status Pengembangan

- **Tahap**: Stabil untuk Penggunaan Lokal & Evaluasi Operasional (Local Production-Ready).
- **Hasil Quality Gate**:
  - Pengujian Otomatis (Pest 5 / PHPUnit 13): **103 tes lulus, 340 assertion** (cakupan: autentikasi, otorisasi role, CRUD katalog, kalkulasi POS atomik, idempotensi token checkout, penanganan stok, filter tanggal, kontrol keamanan OWASP, dan proteksi struk).
  - Pemeriksaan Gaya Kode: Standar Laravel Pint (Clean).
  - Bundling Aset Frontend: Kompilasi Vite 8 berhasil tanpa peringatan kritis.
  - Audit Dependensi: 0 kerentanan dilaporkan oleh `composer audit` dan `npm audit`.

---

## 3. Fitur yang Tersedia

1. **Autentikasi & Pembatasan Sesi**:
   - Login dengan proteksi *rate limiting* berbasis IP dan alamat email (maksimal 5 percobaan gagal dengan penundaan eksponensial).
   - Regenerasi ID sesi secara ketat pasca-login guna mencegah serangan *session fixation*.
   - Invalidation sesi menyeluruh dan pembaruan CSRF token saat logout.
   - Pesan kesalahan login generik tanpa membocorkan keberadaan akun email.
2. **Otorisasi Berbasis Peran (RBAC)**:
   - Dua peran resmi: **Owner** dan **Kasir**.
   - Ditegakkan langsung pada level routing middleware Laravel (`can:`), Form Request `authorize()`, Gate, dan Model Policy.
3. **Katalog Master Data**:
   - Pengelolaan Kategori Barang dengan proteksi penghapusan berkas terelasi (*restrict on delete*).
   - Pengelolaan Produk: SKU (unik), Barcode (unik, opsional), nama, jenis produk (fisik, pulsa, token listrik), satuan, provider digital, nominal, harga modal (HPP), harga jual, batas minimum stok, dan status aktif.
   - Unggah foto produk berformat JPEG/PNG/WebP dengan validasi ukuran maksimal 2 MB (tanpa dukungan format SVG guna mencegah XSS berbasis vektor XML).
   - Deaktivasi aman (*soft deactivation*): produk yang memiliki riwayat transaksi tidak dapat dihapus permanen agar integritas historis audit terjaga.
4. **Manajemen & Mutasi Stok**:
   - Penambahan stok (*restock*) dengan riwayat pencatat dan catatan faktur.
   - Penyesuaian stok opname (*stock adjustment*) dengan kewajiban mengisi alasan tertulis.
   - Pencatatan seluruh mutasi: stok awal, penjualan kasir, restock, dan koreksi opname.
   - Proteksi stok negatif menggunakan transaksi basis data dengan penguncian baris (`lockForUpdate`).
5. **Point of Sale (POS) Kasir**:
   - Pencarian cerdas berbasis nama barang, kode SKU, maupun pemindaian *barcode* secara instan.
   - Keranjang belanja interaktif dengan kalkulasi otomatis di browser dan *fail-closed server validation*.
   - Input khusus layanan digital: nomor handphone untuk pulsa, ID pelanggan / nomor meter untuk token listrik PLN.
   - Pilihan diskon per transaksi: nominal (Rupiah) atau persentase (0–100%) dengan validasi batas total.
   - Pembayaran fleksibel: Cash (hitung kembalian otomatis), Transfer Bank (konfirmasi manual), dan QRIS (konfirmasi manual).
   - Pintasan keyboard operasional: `F2` (fokus pencarian produk), `F4` (kosongkan keranjang), dan `F8` (pembayaran).
   - Tombol pecahan uang tunai cepat (*quick cash chips*) dan deteksi otomatis kekurangan pembayaran tunai (*shortfall alert*).
   - Tata letak responsif: panel keranjang terintegrasi pada layar lebar (>= 1200px) dan *offcanvas floating drawer* pada layar ponsel / tablet.
   - Idempotensi checkout: penggunaan *UUID checkout token* mencegah terbentuknya transaksi ganda jika tombol ditekan berulang kali atau koneksi terputus.
6. **Layanan Digital (Pulsa & Token Listrik)**:
   - Pencatatan alur operasional layanan digital manual (status terpisah: `pending`, `completed`, `failed`).
   - Kasir pemilik transaksi atau Owner dapat mencatat nomor referensi operator / SN (*serial number*) dan 20 digit kode token listrik saat transaksi berhasil diproses di konter.
   - Validasi status final: transaksi digital yang sudah ditandai `completed` atau `failed` terkunci secara permanen dari manipulasi lanjutan.
7. **Riwayat Penjualan & Cetak Ulang Struk**:
   - Filter riwayat berdasarkan invoice, rentang tanggal (dengan normalisasi otomatis jika input tanggal terbalik), kasir, metode pembayaran, dan status layanan digital.
   - Tampilan detail transaksi lengkap dengan audit trail kasir bertugas.
   - Cetak struk belanja thermal mandiri berbasis CSS cetak browser (`@media print`), mendukung opsi lebar kertas 58 mm dan 80 mm tanpa ketergantungan ekstensi pihak ketiga.
   - Cetak ulang struk dilindungi hak akses: Kasir hanya dapat mencetak struk transaksi miliknya sendiri, sedangkan Owner dapat mencetak seluruh struk.
8. **Pengeluaran Operasional Toko**:
   - Pencatatan beban harian (listrik operasional, kebersihan, konsumsi kasir, pemeliharaan toko, dll.).
   - Filter berdasarkan rentang tanggal dan kategori pengeluaran.
   - Terbatas hanya untuk pengguna dengan hak akses Owner.
9. **Dashboard Operasional**:
   - Metrik penjualan hari ini (jumlah transaksi dan akumulasi omzet).
   - Notifikasi stok menipis (*low stock*) dan stok habis (*out of stock*).
   - Grafik penjualan 7 hari terakhir.
   - Panel khusus Owner: kalkulasi laba kotor hari ini, daftar antrean layanan digital yang masih tertunda (*pending*), dan daftar 5 produk terlaris.
10. **Laporan & Ekspor Data**:
    - Laporan Penjualan (Harian, Bulanan, Rentang Kustom).
    - Rekapitulasi per metode pembayaran, per kasir, dan barang terlaris.
    - Laporan Laba Rugi Sederhana berbasis harga modal *snapshot*.
    - Ekspor laporan ke format CSV dengan proteksi *CSV Formula Injection* (sanitasi otomatis karakter `=`, `+`, `-`, `@`, `\t`, dan `\r`).
11. **Manajemen Pengguna (User Management)**:
    - Pengelolaan akun Owner dan Kasir oleh Owner.
    - Aturan proteksi sistem: Owner tidak dapat mengubah perannya sendiri secara sepihak dan sistem menolak penghapusan/penurunan peran jika hanya tersisa satu Owner aktif.

---

## 4. Batasan & Integrasi yang Belum Tersedia

Untuk menjaga ekspektasi dan integritas operasional, hal-hal berikut dicatat sebagai batasan aktual:
- **PPOB Otomatis**: Belum terhubung secara API ke agregator server pulsa/listrik (misal Digiflazz, Mobilepulsa, dsb.). Seluruh transaksi pulsa dan token diproses manual oleh kasir di perangkat fisiknya, lalu statusnya diperbarui pada aplikasi.
- **Payment Gateway Otomatis**: Belum terintegrasi dengan webhook perbankan atau QRIS dinamis (seperti Midtrans, Xendit, atau DOKU). Status Transfer dan QRIS diverifikasi manual oleh kasir melalui mutasi rekening sebelum menekan tombol selesaikan transaksi.
- **Direct ESC/POS Raw Socket**: Pencetakan struk saat ini memanfaatkan fitur *native print dialog* dari web browser ke printer thermal sistem operasi, belum melalui direct TCP/USB ESC/POS command byte printing.
- **Modul Pembelian & Hutang Piutang**: Pengadaan barang persediaan baru dicatat melalui penambahan stok langsung, belum mencakup modul Purchase Order (PO), faktur hutang supplier, tempo pembayaran, atau retur konsinyasi.
- **Manajemen Deposit Tabung/Galon**: Pengelolaan deposit fisik tabung LPG 3kg kosong atau galon air mineral belum memiliki tabel penjaminan terpisah (masih dicatat sebagai komoditas barang dagang biasa).
- **Multi-Cabang (Multi-Store)**: Aplikasi saat ini dioptimalkan untuk toko tunggal (*single outlet*) yang beroperasi tanpa henti 24 jam.

---

## 5. Matriks Hak Akses Peran

| Modul / Tindakan | Owner | Kasir | Implementasi Teknis |
| :--- | :---: | :---: | :--- |
| **Login / Logout** | Ya | Ya | `guest` / `auth` middleware |
| **Melihat Dashboard Utama** | Lengkap | Khusus Dirinya | `DashboardController@index` (HPP & Laba disembunyikan dari Kasir) |
| **Membuka Halaman Kasir (POS)** | Ya | Ya | Gate `use-pos` |
| **Menjalankan Checkout Penjualan** | Ya | Ya | FormRequest `CheckoutRequest` + Gate `use-pos` |
| **Melihat Riwayat Transaksi** | Semua | Milik Sendiri | Gate `view-sales` + Scope query `user_id` pada Kasir |
| **Melihat Rincian Transaksi Tertentu** | Semua | Milik Sendiri | `SalePolicy@view` |
| **Mencetak / Cetak Ulang Struk** | Semua | Milik Sendiri | `SalePolicy@printReceipt` |
| **Memperbarui Status Layanan Digital** | Semua | Transaksi Miliknya | `DigitalServiceDetailPolicy@update` |
| **Katalog Produk (Lihat/Tambah/Ubah/Hapus)** | Ya | Tidak (403) | Gate `manage-catalog` |
| **Melihat Harga Modal (HPP)** | Ya | Tidak (Disembunyikan) | Filter data backend & proteksi view |
| **Kelola Stok (Restock & Opname)** | Ya | Tidak (403) | Gate `manage-catalog` |
| **Melihat Mutasi Stok** | Ya | Tidak (403) | Gate `manage-catalog` |
| **Laporan Penjualan & Laba Rugi** | Ya | Tidak (403) | Gate `view-reports` |
| **Ekspor Laporan CSV** | Ya | Tidak (403) | Gate `view-reports` |
| **Pencatatan Pengeluaran Toko** | Ya | Tidak (403) | Gate `manage-expenses` |
| **Manajemen Pengguna (Akun Kasir/Owner)** | Ya | Tidak (403) | Gate `manage-users` |

---

## 6. Stack Teknologi & Versi Aktual

- **Bahasa Pemrograman**: PHP 8.5.x (PHP 8.5.12 terverifikasi).
- **Framework Utama**: Laravel 13.x (Laravel Framework 13.43.0 terverifikasi).
- **Web Server Runtime**: Apache (XAMPP) / Nginx / PHP Built-in Server.
- **Sistem Basis Data Operasional**: MySQL 8.x / MariaDB 10.x (InnoDB, utf8mb4_unicode_ci).
- **Basis Data Pengujian**: SQLite In-Memory (`:memory:`) terisolasi penuh.
- **Frontend & UI**:
  - AdminLTE 4.10 (via paket `colorlibhq/adminlte-laravel` ^1.7).
  - Bootstrap 5.3.x & Bootstrap Icons 1.13.x.
  - Tailwind CSS 4.3.x (digunakan khusus pada utilitas layout modern `app.css`).
  - Vite 8.x (bundler aset).
- **Pengujian & Kualitas Kode**:
  - Pest PHP 5.3.x & Pest Plugin Laravel.
  - PHPUnit 13.4.x.
  - Laravel Pint 1.27.x.

---

## 7. Persyaratan Lingkungan

Sebelum menjalankan aplikasi, pastikan komputer telah terpasang:
1. **PHP**: Versi `>= 8.3` (Disarankan PHP 8.5).
   - Ekstensi PHP wajib aktif: `pdo_mysql`, `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `bcmath`, `curl`.
2. **Composer**: Versi `>= 2.7` (Composer 2.11 terverifikasi).
3. **Node.js & npm**: Node.js `>= 20.x` (Node v26.13 dan npm 11.20 terverifikasi).
4. **Database Server**: MySQL `>= 8.0` atau MariaDB `>= 10.4` (bawaan XAMPP siap pakai).

---

## 8. Struktur Folder Utama

```text
putra_mandiri/
├── app/
│   ├── Enums/                 # Enum status, role, metode bayar, tipe produk
│   ├── Http/
│   │   ├── Controllers/       # Controller web aplikasi
│   │   ├── Middleware/        # Middleware keamanan (SecurityHeaders, dll)
│   │   └── Requests/          # Validasi Form Request berbahasa Indonesia
│   ├── Models/                # Eloquent models, casting, dan relasi
│   ├── Policies/              # Otorisasi model Sale dan DigitalService
│   ├── Providers/             # Gate definitions, paginator, dan provider Laravel
│   ├── Services/              # Logika bisnis inti (Checkout atomik, stok, digital)
│   └── Support/               # Helper aritmetika uang sen (Money.php)
├── bootstrap/                 # Konfigurasi routing, exception, dan middleware
├── config/                    # Konfigurasi aplikasi, adminlte, store, navigation
├── database/
│   ├── factories/             # Factory model untuk pengujian otomatis
│   ├── migrations/            # Skema tabel Laravel
│   ├── seeders/               # Seeder pengguna awal, kategori, dan data demo
│   ├── putra_mandiri.sql      # Dump skema resmi MySQL/MariaDB untuk phpMyAdmin
│   ├── putra_mandiri.db       # Salinan teks SQL MySQL dengan nama berekstensi .db
│   └── README_IMPORT.md       # Dokumentasi panduan impor database phpMyAdmin
├── public/                    # Document root publik web server
│   ├── build/                 # Kompilasi aset Vite (CSS/JS)
│   └── storage/               # Symlink ke storage/app/public (foto produk)
├── resources/
│   ├── css/                   # Stylesheet kustom
│   ├── js/                    # Skrip AdminLTE dan interaktivitas POS
│   └── views/                 # Blade views antarmuka aplikasi
├── routes/
│   ├── console.php            # Perintah konsol Artisan
│   └── web.php                # Rute web aplikasi dan proteksi gate
├── storage/                   # Penyimpanan privat, log, framework cache
└── tests/
    ├── Feature/               # Feature testing Pest (alur bisnis & keamanan)
    └── Unit/                  # Unit testing fungsi dasar uang dan logika
```

---

## 9. Instalasi & Setup Proyek

### Langkah 1: Kloning / Ekstraksi Repositori
Tempatkan folder proyek di dalam direktori kerja server lokal, misalnya:
`C:\xampp\htdocs\putra_mandiri`

### Langkah 2: Instalasi Dependensi Backend
Buka terminal (PowerShell / Command Prompt / Bash) pada direktori proyek:
```bash
composer install --no-interaction
```

### Langkah 3: Konfigurasi Berkas `.env`
Salin template konfigurasi:
```bash
# Windows
copy .env.example .env

# Linux / macOS
cp .env.example .env
```

Buka berkas `.env` dan pastikan konfigurasi dasar terisi:
```dotenv
APP_NAME="PUTRA MANDIRI"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=putra_mandiri
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

ADMINLTE_DEMO=false
ADMINLTE_DOCS=false
```

Generate application encryption key:
```bash
php artisan key:generate
```

### Langkah 4: Pembuatan Symbolic Link Berkas Publik
Untuk memastikan foto produk yang diunggah dapat diakses oleh browser:
```bash
php artisan storage:link
```

### Langkah 5: Instalasi Dependensi Frontend & Kompilasi Aset
```bash
npm install
npm run build
```

---

## 10. Konfigurasi Basis Data MySQL & Panduan Impor

Aplikasi ini dirancang untuk berjalan pada sistem basis data **MySQL 8.x** atau **MariaDB 10.x** (standar XAMPP).

> [!IMPORTANT]
> **Peringatan Integritas Basis Data Operasional:**
> Jangan pernah menjalankan perintah destruktif seperti `php artisan migrate:fresh`, `php artisan migrate:refresh`, atau `php artisan db:wipe` pada database yang sedang aktif digunakan. Tindakan tersebut akan menghapus seluruh data barang, stok, dan riwayat transaksi secara permanen.

Terdapat dua alur instalasi basis data yang didukung. Pilih salah satu sesuai preferensi lingkungan Anda:

### Alur Utama: Impor Skema via phpMyAdmin (Direkomendasikan untuk XAMPP)
1. Buka antarmuka phpMyAdmin di browser Anda (biasanya di `http://localhost/phpmyadmin`).
2. Masuk ke menu **Databases**, buat basis data baru bernama: `putra_mandiri` dengan collation `utf8mb4_unicode_ci`.
3. Klik nama basis data `putra_mandiri` di panel sebelah kiri agar berstatus aktif.
4. Buka tab **Import**, klik tombol **Choose File**, lalu pilih berkas:
   `database/putra_mandiri.sql`
5. Biarkan opsi lainnya pada nilai default, gulir ke bawah, lalu klik **Import / Go**.
6. **Verifikasi Keberhasilan**:
   - 16 tabel terbentuk: `users`, `password_reset_tokens`, `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `migrations`, `categories`, `products`, `operating_expenses`, `sales`, `sale_items`, `stock_movements`, dan `digital_service_details`.
   - Tabel `categories` secara otomatis terisi 6 kategori awal: Sembako, Mie & Kopi, Rokok, Gas & Galon, Pulsa & Token, Snack & Minuman.
   - Tabel `migrations` secara otomatis terisi 12 riwayat migrasi batch 1. Hal ini menjamin bahwa jika di masa mendatang dijalankan perintah `php artisan migrate`, Laravel tidak akan mengalami bentrok (*table already exists*).

### Alur Alternatif: Setup Menggunakan Laravel Migration & Seeder CLI
Jika Anda mengonfigurasi database kosong melalui terminal tanpa phpMyAdmin:
1. Buat database kosong di MySQL:
   ```sql
   CREATE DATABASE putra_mandiri CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Jalankan migrasi resmi Laravel:
   ```bash
   php artisan migrate --force
   ```
3. Jalankan seeder akun awal dan kategori:
   ```bash
   php artisan db:seed
   ```

*Peringatan:* Jangan mencampur kedua alur instalasi secara tumpang tindih. Jika Anda sudah mengimpor `putra_mandiri.sql`, Anda cukup membuat akun pengguna awal (lihat bagian 11).

---

## 11. Pembuatan Akun Pengguna (Owner & Kasir)

Aplikasi PUTRA MANDIRI dilengkapi seeder yang bersifat *idempotent* dan aman.

Konfigurasikan password awal yang Anda kehendaki pada berkas `.env` lokal:
```dotenv
INITIAL_OWNER_NAME="Owner Putra Mandiri"
INITIAL_OWNER_EMAIL=owner@putramandiri.test
INITIAL_OWNER_PASSWORD=KetikPasswordKuatAndaDisini!

INITIAL_CASHIER_NAME="Muhammad Hardiansah"
INITIAL_CASHIER_EMAIL=hardiansah@putramandiri.test
INITIAL_CASHIER_PASSWORD=KetikPasswordKasirDisini!
```

Jalankan perintah seeder pengguna:
```bash
php artisan db:seed --class=UserSeeder
```

*Catatan Keamanan:*
- Jika `INITIAL_*_PASSWORD` dikosongkan pada `.env`, seeder secara otomatis menghasilkan password acak kriptografis 16 karakter dan menampilkannya **hanya satu kali** pada output konsol.
- Jika akun dengan email tersebut sudah ada di tabel `users`, seeder tidak akan menimpa password yang sudah ada.

Untuk menambahkan barang demonstrasi pada lingkungan lokal/evaluasi:
```bash
php artisan db:seed --class=DemoProductSeeder
```
*(Data demo hanya berisi katalog barang contoh berawalan kode `DEMO-`, tanpa membuat transaksi contoh).*

---

## 12. Menjalankan Aplikasi Secara Lokal

1. **Jalankan Web Server Laravel**:
   ```bash
   php artisan serve
   ```
   Aplikasi akan aktif di alamat: `http://localhost:8000` (atau gunakan Virtual Host Apache XAMPP jika dikonfigurasi).
2. **Jalankan Asset Watcher (Opsional saat pengembangan frontend)**:
   ```bash
   npm run dev
   ```
3. Buka browser dan arahkan ke: `http://localhost:8000/login`

---

## 13. Infrastruktur: Sesi, Cache, Antrean, dan Scheduler

- **Sesi (`SESSION_DRIVER=database`)**:
  - Sesi web disimpan di tabel `sessions` MySQL, memberikan persistensi yang stabil pada toko 24 jam meskipun server web di-restart.
  - Waktu kedaluwarsa sesi diatur 120 menit (`SESSION_LIFETIME=120`).
- **Cache (`CACHE_STORE=database`)**:
  - Memanfaatkan tabel `cache` dan `cache_locks`.
  - Data mutasi stok dan penjualan kasir **tidak di-cache** secara membabi buta guna menjamin konsistensi ACID *real-time*.
- **Antrean (`QUEUE_CONNECTION=database`)**:
  - Disiapkan untuk penanganan *background job* melalui tabel `jobs`.
- **Scheduler**:
  - Didefinisikan pada `routes/console.php` untuk pembersihan token kedaluwarsa dan pemeliharaan rutin. Jalankan via cron server jika dideploy ke produksi.

---

## 14. Alur Bisnis Utama

### A. Alur Transaksi Kasir (POS)
1. Kasir masuk (*login*) menggunakan akunnya.
2. Membuka menu **Kasir (POS)**.
3. Kasir mencari produk dengan mengetikkan nama / SKU, atau langsung menembakkan scanner barcode.
4. Menekan `Enter` atau mengklik kartu barang untuk memasukkan ke keranjang.
5. Untuk komoditas fisik: kasir dapat mengubah jumlah (*quantity*) barang. Stok divalidasi langsung agar tidak melebihi stok toko.
6. Untuk produk digital (Pulsa/Token):
   - Kuantitas terkunci 1 unit per item layanan.
   - Wajib memasukkan nomor HP tujuan untuk pulsa.
   - Wajib memasukkan minimal salah satu dari ID Pelanggan atau Nomor Meter untuk token listrik.
7. Diskon Transaksi (jika ada): kasir memilih jenis diskon (Nominal Rupiah atau Persentase) dan menginput nilainya.
8. Pemilihan Metode Pembayaran:
   - **Cash**: Kasir menginput uang tunai yang diterima (bisa menggunakan tombol cepat *quick cash*). Sistem menghitung kembalian. Jika uang tunai kurang, tombol transaksi terkunci dan peringatan *shortfall alert* muncul.
   - **Transfer / QRIS**: Kasir memverifikasi penerimaan dana pada rekening/alat EDC toko. Nominal pembayaran terkunci sama persis dengan total tagihan (tanpa kembalian).
9. Menyelesaikan Transaksi:
   - Kasir menekan tombol **Selesaikan Transaksi** (atau pintasan `F8`).
   - Sistem mengirim payload ke server bersama `checkout_token` unik.
   - Server mengunci baris produk (`lockForUpdate`), memverifikasi stok, menghitung ulang seluruh harga dan diskon dari data master produk, memotong stok fisik, membuat mutasi stok, mencatat header `sales`, detail `sale_items`, dan detail layanan digital secara atomik di dalam database transaction.
   - Setelah sukses, kasir otomatis diarahkan ke halaman detail transaksi dengan opsi langsung mencetak struk thermal.

### B. Alur Pemenuhan Layanan Digital (Pulsa & Token Listrik)
1. Transaksi penjualan kasir berhasil dicatat dengan status layanan digital awal: `pending`.
2. Kasir memproses pengisian pulsa/token melalui aplikasi dealer / perangkat fisik konter toko.
3. Setelah transaksi di konter sukses:
   - Buka menu **Layanan Digital** atau buka rincian transaksi terkait.
   - Klik **Tandai Status**.
   - Pilih status: `completed` (Selesai/Terkirim) atau `failed` (Gagal).
   - Masukkan nomor referensi provider (SN / nomor resi pengisian) dan 20 digit kode token listrik PLN.
   - Klik **Simpan Status**.
4. Status layanan diperbarui menjadi permanen dan pendapatan layanan terkait otomatis diakui dalam laporan laba kotor toko.

### C. Alur Mutasi & Opname Stok
1. Pemilik toko membuka menu **Barang** > pilih produk > klik tombol **Kelola Stok**.
2. **Penambahan Stok (Restock)**: Digunakan saat barang baru datang dari supplier. Masukkan jumlah dan catatan faktur.
3. **Penyesuaian Stok (Opname)**: Digunakan saat penghitungan fisik toko menemukan selisih (rusak, hilang, bocor, atau salah hitung). Masukkan stok fisik riil saat ini dan **wajib** mencantumkan alasan penyesuaian.
4. Seluruh pergerakan stok tercatat permanen di menu **Mutasi Stok**.

---

## 15. Definisi Laporan Finansial Toko

Untuk menghindari kerancuan istilah akuntansi pada toko kebutuhan harian:
- **Penjualan Kotor (Gross Sales)**: Akumulasi subtotal harga jual seluruh barang sebelum dikurangi diskon.
- **Total Diskon**: Akumulasi potongan diskon transaksi (dialokasikan secara proporsional ke setiap baris barang, dengan pembulatan sen dibebankan pada baris terakhir).
- **Penerimaan Penjualan (Net Sales / Cash Inflow)**: Total uang transaksi yang tercatat diterima kasir (`Gross Sales - Diskon`).
- **Pendapatan Diakui (Recognized Revenue)**: Akumulasi nilai bersih dari barang fisik yang terjual ditambah nilai transaksi layanan digital yang berstatus `completed`. *(Layanan digital yang masih `pending` atau `failed` belum diakui sebagai pendapatan agar laba tidak semu)*.
- **HPP Snapshot (Harga Pokok Penjualan)**: Akumulasi `cost_price` (harga modal) yang terkunci secara permanen pada saat transaksi terjadi dikalikan kuantitas barang fisik dan layanan digital yang berstatus selesai.
- **Laba Kotor (Gross Profit)**: `Pendapatan Diakui - HPP Snapshot`.
- **Pengeluaran Operasional**: Akumulasi biaya rutin toko (listrik, kebersihan, operasional kasir, perlengkapan toko). *Catatan: Pengadaan persediaan barang dagang tidak dicatat di modul pengeluaran operasional agar tidak terjadi pencatatan ganda dengan HPP*.
- **Laba Bersih Sederhana**: `Laba Kotor - Pengeluaran Operasional`.
- **Zona Waktu & Tanggal**: Seluruh transaksi dan filter tanggal menggunakan zona waktu resmi toko: **WIB (`Asia/Jakarta`)** dengan rentang inklusif `00:00:00` sampai `23:59:59`.

---

## 16. Cetak Struk Belanja & Batasan Printer

- Format struk belanja diakses melalui rute `/sales/{sale}/receipt`.
- Halaman struk menggunakan tata letak independen bebas elemen navigasi (*clean print layout*).
- Dilengkapi tombol pratinjau untuk beralih antara lebar kertas thermal **58 mm** dan **80 mm**.
- Menggunakan standar font monospace monokrom (`Courier New` / `Lucida Console`) dengan perataan angka tabel (*tabular nums*).
- Jika pada transaksi terdapat layanan token listrik yang sudah selesai, nomor token 20 digit dicetak tebal di dalam kotak khusus pada struk.
- Catatan operasional: Printer thermal USB/Bluetooth harus diatur sebagai printer aktif pada sistem operasi komputer kasir agar dialog print browser dapat langsung mencetak.

---

## 17. Ringkasan Penerapan Kontrol Keamanan (OWASP Top 10:2025)

| Kategori OWASP Top 10:2025 | Kontrol yang Diterapkan pada PUTRA MANDIRI | Bukti Verifikasi / Uji |
| :--- | :--- | :--- |
| **A01: Broken Access Control** | Otorisasi server-side melalui Gate (`manage-catalog`, `view-sales`, `view-reports`, dll.) dan Policy (`SalePolicy`, `DigitalServiceDetailPolicy`). Kasir tidak dapat membuka rute Owner atau melihat transaksi kasir lain. Pencegahan IDOR pada rute struk dan detail transaksi. Null-safe navigation pada relasi transaksi digital. | 16 tes otorisasi pada `AuthorizationTest.php` dan `SecurityControlsTest.php`. |
| **A02: Security Misconfiguration** | Pemasangan middleware `SecurityHeaders` (`X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection: 0`, `Referrer-Policy`, `Permissions-Policy`). Penonaktifan rute demo dan dokumentasi bawaan AdminLTE (`ADMINLTE_DEMO=false`, `ADMINLTE_DOCS=false`). Penonaktifan endpoint publik `serve` pada disk private di `config/filesystems.php`. Sesi cookie berbendera `HttpOnly` dan `SameSite=lax`. | Respon header diverifikasi pada `SecurityControlsTest.php`; rute demo mengembalikan status 404. |
| **A03: Software Supply Chain Failures** | Dependensi PHP dan Node.js terkunci melalui `composer.lock` dan `package-lock.json`. Tidak menggunakan paket pihak ketiga yang mencurigakan atau skrip instalasi eksternal tak tepercaya. | `composer audit` (0 vulnerability) & `npm audit` (0 vulnerability). |
| **A04: Cryptographic Failures** | Hashing password pengguna menggunakan algoritma Bcrypt dengan cost factor 12 rounds (`BCRYPT_ROUNDS=12`). Tidak menyimpan password plaintext. Kunci enkripsi `APP_KEY` dikelola melalui file `.env` privat dan tidak pernah ditanam di source code. | Teruji pada `AuthTest.php` dan `UserSeeder`. |
| **A05: Injection** | Seluruh query Eloquent dan Query Builder menggunakan PDO parameter binding. Tidak ada interpolasi string pengguna ke raw SQL. Seluruh output pada Blade template menggunakan escaping HTML `{{ }}` (tanpa sintaks raw unescaped). Sanitasi formula CSV pada ekspor laporan (`=`, `+`, `-`, `@`, `\t`, `\r`). | Teruji pada `ReportTest.php` dan inspeksi codebase statis. |
| **A06: Insecure Design** | Perhitungan total transaksi, diskon, dan kembalian sepenuhnya dilakukan di server (`SaleCheckoutService`). Nilai harga dari browser diabaikan. Snapshot harga modal terkunci saat checkout. Pengurangan stok menggunakan row-level lock (`lockForUpdate`) dalam database transaction untuk mencegah penjualan melebihi stok (*race condition*). Token checkout mencegah transaksi ganda. | Teruji pada `CheckoutTest.php` dan `CheckoutPaymentTest.php`. |
| **A07: Authentication Failures** | Rate limiting login berbasis IP dan email (maksimal 5 percobaan). Regenerasi session ID saat login (`session()->regenerate()`) dan invalidasi sesi saat logout. Pesan kegagalan login generik. | Teruji pada `AuthTest.php` dan `SecurityControlsTest.php`. |
| **A08: Software & Data Integrity Failures** | Validasi berkas unggahan foto produk (hanya JPEG/PNG/WebP, maksimal 2 MB, tanpa SVG). Penyimpanan berkas menggunakan nama acak hash di disk publik. Validasi transisi status layanan digital hanya dapat berubah dari `pending` ke `completed` atau `failed`. | Teruji pada `CategoryProductTest.php` dan `DigitalServiceTest.php`. |
| **A09: Security Logging & Alerting Failures** | Logging terstruktur tanpa data sensitif untuk: percobaan login gagal, lockout rate limit, perubahan peran akun pengguna, penyesuaian stok opname (*stock adjustment*), dan perubahan status layanan digital. Log disimpan melalui Laravel stack log channel dengan rotasi. | Teruji dengan mock spy pada `SecurityControlsTest.php`. |
| **A10: Mishandling of Exceptional Conditions** | Seluruh operasi multi-tabel dibungkus dalam `DB::transaction`. Jika terjadi kegagalan atau exception, sistem melakukan *rollback* penuh sehingga tidak pernah terjadi transaksi setengah tersimpan. Halaman error 403, 404, dan 500 disajikan secara ramah pengguna tanpa membocorkan database stack trace ke publik. | Teruji pada `CheckoutTest.php` (transaksi gagal tidak mengurangi stok) dan view error kustom. |

---

## 18. Risiko Keamanan Tersisa (*Residual Risks*)

Sebagai wujud kepatuhan terhadap prinsip kejujuran rekayasa perangkat lunak, risiko tersisa berikut perlu dipahami oleh pengelola sistem:
1. **Lingkungan HTTP vs HTTPS**: Pengaturan cookie `SESSION_SECURE_COOKIE` secara default bernilai `false` pada pengembangan lokal. Jika dideploy ke lingkungan produksi publik, wajib diaktifkan (`SESSION_SECURE_COOKIE=true`) bersama sertifikat SSL/TLS (HTTPS) agar cookie tidak dapat disadap di jaringan publik.
2. **Ketergantungan Autentikasi Kasir Bersama**: Pada operasional toko 24 jam dengan pergantian shift cepat, kasir berpotensi lupa melakukan logout sebelum kasir shift berikutnya bertugas. Disarankan mengedukasi kasir agar selalu menekan tombol logout setiap pergantian giliran jaga.
3. **Konfirmasi Manual Transfer/QRIS**: Karena belum ada integrasi webhook bank otomatis, toko bergantung pada ketelitian kasir dalam memeriksa mutasi rekening sebelum memvalidasi transaksi nontunai.

---

## 19. Pengujian & Perintah Verifikasi

Aplikasi menggunakan basis data terpisah **SQLite In-Memory** saat pengujian otomatis dijalankan, sehingga tidak akan memengaruhi atau menyentuh data operasional toko pada MySQL.

Jalankan rangkaian tes lengkap melalui terminal:
```bash
php artisan test
```

Jalankan pengujian terfokus:
```bash
# Pengujian kontrol keamanan OWASP
php artisan test --filter=SecurityControlsTest

# Pengujian alur transaksi POS
php artisan test --filter=CheckoutTest

# Pengujian otorisasi peran Owner dan Kasir
php artisan test --filter=AuthorizationTest
```

Jalankan linter formatter kode:
```bash
vendor/bin/pint --test
```

Jalankan audit keamanan paket dependensi:
```bash
composer audit
npm audit
```

---

## 20. Prosedur Backup & Pemulihan Operasional

### Backup Rutin Basis Data (Harian)
Gunakan mysqldump melalui terminal server:
```bash
# Windows XAMPP CLI
C:\xampp\mysql\bin\mysqldump.exe -u root -p putra_mandiri > C:\backup\putra_mandiri_%date:~10,4%%date:~4,2%%date:~7,2%.sql

# Linux Server
mysqldump -u putra_user -p putra_mandiri > /backup/putra_mandiri_$(date +%Y%m%d).sql
```

### Backup Berkas Unggahan Produk
Salin direktori foto produk secara berkala:
`storage/app/public/products`

### Prosedur Pemulihan (Disaster Recovery)
1. Buat database baru di MySQL:
   ```sql
   CREATE DATABASE putra_mandiri CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
2. Impor berkas dump SQL terakhir:
   ```bash
   mysql -u root -p putra_mandiri < C:\backup\berkas_backup_terakhir.sql
   ```
3. Pulihkan berkas gambar ke folder `storage/app/public/products` dan jalankan `php artisan storage:link`.

---

## 21. Troubleshooting Berdasarkan Masalah Nyata

1. **Error: "Unable to locate file in Vite manifest"**:
   - *Penyebab*: Aset frontend belum dikompilasi ke direktori `public/build`.
   - *Solusi*: Jalankan `npm run build` di terminal.
2. **Foto produk tidak muncul di browser**:
   - *Penyebab*: Tautan symlink storage belum dibuat.
   - *Solusi*: Jalankan `php artisan storage:link`. Pastikan direktori `public/storage` terbentuk.
3. **Gagal login dengan pesan "Terlalu banyak percobaan login"**:
   - *Penyebab*: Rate limiter mendeteksi lebih dari 5 kali kegagalan login berturut-turut.
   - *Solusi*: Tunggu durasi jeda yang tertera di layar (default 60 detik) atau bersihkan cache via `php artisan cache:clear` jika dalam lingkungan lokal.
4. **Kasir mendapat error 403 Forbidden saat membuka menu**:
   - *Penyebab*: Kasir mencoba mengakses menu katalog produk, kelola stok, laporan, pengeluaran, atau pengguna yang memang dikhususkan untuk Owner.
   - *Solusi*: Hal ini merupakan perilaku normal otorisasi sistem. Arahkan kasir ke menu POS, Riwayat Transaksi, atau Layanan Digital.

---

## 22. Informasi Lisensi

Aplikasi PUTRA MANDIRI dilisensikan di bawah lisensi terbuka [MIT License](LICENSE).
Komponen AdminLTE 4 dilisensikan di bawah lisensi MIT oleh ColorlibHQ.
Ikon antarmuka menggunakan Bootstrap Icons di bawah lisensi MIT.
