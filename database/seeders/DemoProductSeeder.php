<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DemoProductSeeder extends Seeder
{
    /**
     * Data barang demonstrasi. Jalankan secara eksplisit:
     * php artisan db:seed --class=DemoProductSeeder
     * Jangan dijalankan otomatis di DatabaseSeeder.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->error('Seeder demo hanya boleh dijalankan pada lingkungan local atau testing.');

            return;
        }

        $sembako = Category::query()->firstOrCreate(['name' => 'Sembako'], ['is_active' => true]);
        $snack = Category::query()->firstOrCreate(['name' => 'Snack & Minuman'], ['is_active' => true]);
        $digital = Category::query()->firstOrCreate(['name' => 'Pulsa & Token'], ['is_active' => true]);

        $products = [
            [
                'category_id' => $sembako->id,
                'sku' => 'DEMO-BERAS-5KG',
                'name' => 'DEMO Beras Premium 5 kg',
                'type' => ProductType::Physical,
                'unit' => 'karung',
                'cost_price' => 62000,
                'sell_price' => 68000,
                'stock' => 10,
                'min_stock' => 2,
            ],
            [
                'category_id' => $sembako->id,
                'sku' => 'DEMO-MINYAK-1L',
                'name' => 'DEMO Minyak Goreng 1 L',
                'type' => ProductType::Physical,
                'unit' => 'botol',
                'cost_price' => 15000,
                'sell_price' => 17000,
                'stock' => 12,
                'min_stock' => 3,
            ],
            [
                'category_id' => $snack->id,
                'sku' => 'DEMO-TEH-BOTOL',
                'name' => 'DEMO Teh Botol 350 ml',
                'type' => ProductType::Physical,
                'unit' => 'botol',
                'cost_price' => 3000,
                'sell_price' => 4000,
                'stock' => 24,
                'min_stock' => 6,
            ],
            [
                'category_id' => $digital->id,
                'sku' => 'DEMO-PULSA-TSEL-10K',
                'name' => 'DEMO Pulsa Telkomsel 10.000',
                'type' => ProductType::Pulsa,
                'unit' => 'transaksi',
                'provider' => 'Telkomsel',
                'nominal' => 10000,
                'cost_price' => 10500,
                'sell_price' => 12000,
                'stock' => 0,
                'min_stock' => 0,
            ],
            [
                'category_id' => $digital->id,
                'sku' => 'DEMO-TOKEN-PLN-20K',
                'name' => 'DEMO Token Listrik PLN 20.000',
                'type' => ProductType::Token,
                'unit' => 'transaksi',
                'provider' => 'PLN',
                'nominal' => 20000,
                'cost_price' => 20500,
                'sell_price' => 22000,
                'stock' => 0,
                'min_stock' => 0,
            ],
        ];

        foreach ($products as $product) {
            Product::query()->firstOrCreate(
                ['sku' => $product['sku']],
                [...$product, 'is_active' => true],
            );
        }

        $this->command?->info('Data barang demonstrasi diproses (produk yang sudah ada tidak diubah).');
    }
}
