<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Enam kategori awal. firstOrCreate menjaga seeder tetap idempotent.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Sembako', 'description' => 'Beras, minyak, gula, tepung, dan kebutuhan pokok lainnya.'],
            ['name' => 'Mie & Kopi', 'description' => 'Mie instan, kopi sachet, dan minuman seduh.'],
            ['name' => 'Rokok', 'description' => 'Berbagai merek rokok.'],
            ['name' => 'Gas & Galon', 'description' => 'Gas LPG dan air galon.'],
            ['name' => 'Pulsa & Token', 'description' => 'Layanan pulsa dan token listrik (pencatatan manual).'],
            ['name' => 'Snack & Minuman', 'description' => 'Makanan ringan dan minuman kemasan.'],
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description'], 'is_active' => true],
            );
        }
    }
}
