<?php

return [

    'menu' => [

        [
            'text' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'bi bi-speedometer2',
            'active' => ['dashboard'],
        ],
        [
            'text' => 'Kasir (POS)',
            'route' => 'pos.index',
            'icon' => 'bi bi-cart3',
            'can' => 'use-pos',
            'active' => ['pos*'],
        ],
        [
            'text' => 'Riwayat Transaksi',
            'route' => 'sales.index',
            'icon' => 'bi bi-receipt',
            'can' => 'view-sales',
            'active' => ['sales', 'sales/*'],
        ],
        [
            'text' => 'Layanan Digital',
            'route' => 'digital.index',
            'icon' => 'bi bi-phone',
            'can' => 'view-sales',
            'active' => ['digital*'],
        ],

        ['header' => 'MASTER DATA'],
        [
            'text' => 'Barang',
            'route' => 'products.index',
            'icon' => 'bi bi-box-seam',
            'can' => 'manage-catalog',
            'active' => ['products', 'products/*'],
        ],
        [
            'text' => 'Kategori',
            'route' => 'categories.index',
            'icon' => 'bi bi-tags',
            'can' => 'manage-catalog',
            'active' => ['categories', 'categories/*'],
        ],
        [
            'text' => 'Mutasi Stok',
            'route' => 'stock-movements.index',
            'icon' => 'bi bi-arrow-left-right',
            'can' => 'manage-catalog',
            'active' => ['stock-movements*'],
        ],

        ['header' => 'LAPORAN'],
        [
            'text' => 'Laporan Penjualan',
            'route' => 'reports.sales',
            'icon' => 'bi bi-graph-up',
            'can' => 'view-reports',
            'active' => ['reports/sales*'],
        ],
        [
            'text' => 'Laba Rugi',
            'route' => 'reports.profit-loss',
            'icon' => 'bi bi-calculator',
            'can' => 'view-reports',
            'active' => ['reports/profit-loss*'],
        ],
        [
            'text' => 'Pengeluaran',
            'route' => 'expenses.index',
            'icon' => 'bi bi-wallet2',
            'can' => 'manage-expenses',
            'active' => ['expenses', 'expenses/*'],
        ],

        ['header' => 'PENGATURAN'],
        [
            'text' => 'Pengguna',
            'route' => 'users.index',
            'icon' => 'bi bi-people',
            'can' => 'manage-users',
            'active' => ['users', 'users/*'],
        ],
    ],

];
