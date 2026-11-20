-- =====================================================================
-- PUTRA MANDIRI - SKEMA DATABASE MYSQL & MARIADB
-- =====================================================================
-- Aplikasi   : PUTRA MANDIRI (Point of Sale & Toko 24 Jam)
-- Framework  : Laravel 13 (PHP 8.5)
-- Format     : MySQL 8.x / MariaDB 10.x (InnoDB, utf8mb4_unicode_ci)
-- Karakter   : UTF-8 tanpa BOM
-- Tujuan     : Instalasi awal pada database kosong melalui phpMyAdmin
-- Catatan    : Tidak memuat DROP DATABASE, DROP TABLE, atau TRUNCATE.
--              Tabel disusun sesuai urutan dependensi foreign key
--              sehingga dapat dieksekusi tanpa menonaktifkan foreign key checks.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. TABEL: users
-- Mengelola akun pengguna (Owner dan Kasir).
-- ---------------------------------------------------------------------
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'kasir',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. TABEL: password_reset_tokens
-- Penyimpanan token reset password Laravel.
-- ---------------------------------------------------------------------
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. TABEL: sessions
-- Penyimpanan session pengguna ketika menggunakan driver database.
-- ---------------------------------------------------------------------
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. TABEL: cache
-- Penyimpanan cache aplikasi ketika menggunakan driver database.
-- ---------------------------------------------------------------------
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. TABEL: cache_locks
-- Penguncian atomik untuk cache Laravel.
-- ---------------------------------------------------------------------
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. TABEL: jobs
-- Antrean pekerjaan background job Laravel.
-- ---------------------------------------------------------------------
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. TABEL: job_batches
-- Pengelompokan antrean batch job Laravel.
-- ---------------------------------------------------------------------
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. TABEL: failed_jobs
-- Pencatatan job antrean yang gagal dieksekusi.
-- ---------------------------------------------------------------------
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`, `queue`, `failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. TABEL: migrations
-- Pencatatan riwayat eksekusi migration Laravel.
-- ---------------------------------------------------------------------
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. TABEL: categories
-- Kategori produk barang fisik dan layanan digital.
-- ---------------------------------------------------------------------
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. TABEL: products
-- Data master produk fisik, pulsa, dan token listrik.
-- ---------------------------------------------------------------------
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `sku` varchar(50) NOT NULL,
  `barcode` varchar(64) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'physical',
  `unit` varchar(30) NOT NULL DEFAULT 'pcs',
  `provider` varchar(50) DEFAULT NULL,
  `nominal` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sell_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `image_path` varchar(2048) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_is_active_name_index` (`is_active`, `name`),
  KEY `products_type_index` (`type`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 12. TABEL: operating_expenses
-- Pencatatan pengeluaran operasional toko.
-- ---------------------------------------------------------------------
CREATE TABLE `operating_expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `expense_date` date NOT NULL,
  `category` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `operating_expenses_user_id_foreign` (`user_id`),
  KEY `operating_expenses_expense_date_category_index` (`expense_date`, `category`),
  CONSTRAINT `operating_expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 13. TABEL: sales
-- Data transaksi penjualan kasir (POS).
-- ---------------------------------------------------------------------
CREATE TABLE `sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(40) NOT NULL,
  `checkout_token` varchar(64) DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `discount_type` varchar(10) DEFAULT NULL,
  `discount_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `payment_method` varchar(10) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL,
  `change_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_invoice_number_unique` (`invoice_number`),
  UNIQUE KEY `sales_checkout_token_unique` (`checkout_token`),
  KEY `sales_user_id_foreign` (`user_id`),
  KEY `sales_created_at_index` (`created_at`),
  KEY `sales_user_id_created_at_index` (`user_id`, `created_at`),
  KEY `sales_payment_method_index` (`payment_method`),
  KEY `sales_discount_type_index` (`discount_type`),
  CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 14. TABEL: sale_items
-- Rincian baris barang atau layanan dalam setiap transaksi penjualan.
-- ---------------------------------------------------------------------
CREATE TABLE `sale_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `product_name` varchar(150) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `product_type` varchar(20) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `cost_price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  KEY `sale_items_product_type_index` (`product_type`),
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 15. TABEL: stock_movements
-- Log audit perubahan stok (stok awal, penjualan, restock, penyesuaian).
-- ---------------------------------------------------------------------
CREATE TABLE `stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `sale_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(20) NOT NULL,
  `quantity` int(11) NOT NULL,
  `stock_before` int(11) NOT NULL,
  `stock_after` int(11) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_product_id_foreign` (`product_id`),
  KEY `stock_movements_user_id_foreign` (`user_id`),
  KEY `stock_movements_sale_id_foreign` (`sale_id`),
  KEY `stock_movements_product_id_created_at_index` (`product_id`, `created_at`),
  KEY `stock_movements_type_index` (`type`),
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `stock_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_movements_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 16. TABEL: digital_service_details
-- Detail pemenuhan manual transaksi digital (pulsa dan token PLN).
-- ---------------------------------------------------------------------
CREATE TABLE `digital_service_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_item_id` bigint(20) unsigned NOT NULL,
  `service_type` varchar(20) NOT NULL,
  `provider` varchar(50) DEFAULT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `customer_id` varchar(50) DEFAULT NULL,
  `meter_number` varchar(50) DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `provider_reference` varchar(100) DEFAULT NULL,
  `token_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `completed_by_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `digital_service_details_sale_item_id_unique` (`sale_item_id`),
  KEY `digital_service_details_completed_by_id_foreign` (`completed_by_id`),
  KEY `digital_service_details_status_index` (`status`),
  KEY `digital_service_details_service_type_index` (`service_type`),
  CONSTRAINT `digital_service_details_sale_item_id_foreign` FOREIGN KEY (`sale_item_id`) REFERENCES `sale_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `digital_service_details_completed_by_id_foreign` FOREIGN KEY (`completed_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DATA AWAL (SEED DATA REFERENSI)
-- =====================================================================

-- ---------------------------------------------------------------------
-- Data Awal: Kategori Produk (6 Kategori Standar Toko PUTRA MANDIRI)
-- ---------------------------------------------------------------------
INSERT INTO `categories` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sembako', 'Beras, minyak, gula, tepung, dan kebutuhan pokok lainnya.', 1, '2026-11-20 00:00:00', '2026-11-20 00:00:00'),
(2, 'Mie & Kopi', 'Mie instan, kopi sachet, dan minuman seduh.', 1, '2026-11-20 00:00:00', '2026-11-20 00:00:00'),
(3, 'Rokok', 'Berbagai merek rokok.', 1, '2026-11-20 00:00:00', '2026-11-20 00:00:00'),
(4, 'Gas & Galon', 'Gas LPG dan air galon.', 1, '2026-11-20 00:00:00', '2026-11-20 00:00:00'),
(5, 'Pulsa & Token', 'Layanan pulsa dan token listrik (pencatatan manual).', 1, '2026-11-20 00:00:00', '2026-11-20 00:00:00'),
(6, 'Snack & Minuman', 'Makanan ringan dan minuman kemasan.', 1, '2026-11-20 00:00:00', '2026-11-20 00:00:00');

-- ---------------------------------------------------------------------
-- Data Awal: Sinkronisasi Migration Laravel
-- Memastikan php artisan migrate mengenali baseline skema tanpa tabrakan.
-- ---------------------------------------------------------------------
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_30_000000_redact_secrets_in_adminlte_activity_log', 1),
(5, '2026_11_19_181148_add_role_to_users_table', 1),
(6, '2026_11_19_181149_create_categories_table', 1),
(7, '2026_11_19_181150_create_products_table', 1),
(8, '2026_11_19_181151_create_sales_table', 1),
(9, '2026_11_19_181155_create_sale_items_table', 1),
(10, '2026_11_19_181156_create_stock_movements_table', 1),
(11, '2026_11_19_181157_create_digital_service_details_table', 1),
(12, '2026_11_19_181158_create_operating_expenses_table', 1);

-- Catatan Keamanan Akun:
-- Akun Owner dan Kasir (Muhammad Hardiansah) sengaja tidak di-hardcode
-- dalam file SQL ini demi menjaga keamanan hash password.
-- Jalankan perintah: php artisan db:seed --class=UserSeeder
-- setelah mengimpor database untuk membuat akun dengan password aman.
