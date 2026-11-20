<?php

return [

    'name' => env('STORE_NAME', 'PUTRA MANDIRI'),
    'address' => env('STORE_ADDRESS', ''),
    'phone' => env('STORE_PHONE', ''),
    'receipt_footer' => env('STORE_RECEIPT_FOOTER', 'Terima kasih telah berbelanja di PUTRA MANDIRI.'),
    'receipt_width_mm' => (int) env('STORE_RECEIPT_WIDTH_MM', 58),

    'digital_manual_notice' => 'Pencatatan layanan digital dilakukan secara manual. Aplikasi belum terhubung ke provider pulsa atau token listrik.',

    'initial_users' => [
        'owner' => [
            'name' => env('INITIAL_OWNER_NAME', 'Owner Putra Mandiri'),
            'email' => env('INITIAL_OWNER_EMAIL', 'owner@putramandiri.test'),
            'password' => env('INITIAL_OWNER_PASSWORD'),
        ],
        'kasir' => [
            'name' => env('INITIAL_CASHIER_NAME', 'Muhammad Hardiansah'),
            'email' => env('INITIAL_CASHIER_EMAIL', 'hardiansah@putramandiri.test'),
            'password' => env('INITIAL_CASHIER_PASSWORD'),
        ],
    ],

];
