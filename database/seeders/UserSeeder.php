<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Membuat akun Owner dan Kasir awal. Seeder aman dijalankan ulang:
     * akun yang sudah ada tidak diubah dan password lama tidak ditimpa.
     */
    public function run(): void
    {
        $initialUsers = config('store.initial_users');

        $this->seedUser('owner', UserRole::Owner, $initialUsers['owner'] ?? []);
        $this->seedUser('kasir', UserRole::Kasir, $initialUsers['kasir'] ?? []);
    }

    /**
     * @param  array{name?: ?string, email?: ?string, password?: ?string}  $data
     */
    private function seedUser(string $key, UserRole $role, array $data): void
    {
        $name = $data['name'] ?? ($key === 'owner' ? 'Owner Putra Mandiri' : 'Muhammad Hardiansah');
        $email = $data['email'] ?? ($key === 'owner' ? 'owner@putramandiri.test' : 'hardiansah@putramandiri.test');

        $existing = User::query()->where('email', $email)->first();

        if ($existing !== null) {
            $this->command?->info("Akun {$key} ({$email}) sudah ada; tidak diubah.");

            return;
        }

        $configuredPassword = $data['password'] ?? null;
        $generated = false;

        if (blank($configuredPassword)) {
            $configuredPassword = Str::random(16);
            $generated = true;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $configuredPassword,
            'role' => $role->value,
        ]);

        $this->command?->info("Akun {$key} ({$email}) berhasil dibuat dengan role {$role->label()}.");

        if ($generated) {
            $this->command?->warn("Password awal {$key}: {$configuredPassword} (hanya ditampilkan sekali, segera ganti setelah login).");
        }
    }
}
