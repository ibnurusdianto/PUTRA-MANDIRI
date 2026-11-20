<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed dasar aplikasi: akun awal dan kategori.
     * Seeder ini aman dijalankan berulang tanpa mereset data pengguna.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            UserSeeder::class,
        ]);
    }
}
