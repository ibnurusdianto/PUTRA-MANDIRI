<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('seeder akun dan kategori dapat dijalankan ulang tanpa merusak data', function () {
    config([
        'store.initial_users.owner.name' => 'Owner Putra Mandiri',
        'store.initial_users.owner.email' => 'owner@putramandiri.test',
        'store.initial_users.owner.password' => 'PasswordOwner123',
        'store.initial_users.kasir.name' => 'Muhammad Hardiansah',
        'store.initial_users.kasir.email' => 'hardiansah@putramandiri.test',
        'store.initial_users.kasir.password' => 'PasswordKasir123',
    ]);

    $this->seed();

    $owner = User::query()->where('email', 'owner@putramandiri.test')->firstOrFail();
    $kasir = User::query()->where('email', 'hardiansah@putramandiri.test')->firstOrFail();

    expect($owner->name)->toBe('Owner Putra Mandiri')
        ->and($owner->isOwner())->toBeTrue()
        ->and($kasir->name)->toBe('Muhammad Hardiansah')
        ->and($kasir->isKasir())->toBeTrue()
        ->and(Hash::check('PasswordOwner123', $owner->password))->toBeTrue()
        ->and(Category::query()->count())->toBe(6);

    $owner->update(['name' => 'Nama Diubah', 'password' => 'PasswordBaru456']);
    $savedHash = $owner->fresh()->password;

    $this->seed();

    $owner->refresh();

    expect($owner->name)->toBe('Nama Diubah')
        ->and($owner->password)->toBe($savedHash)
        ->and(Hash::check('PasswordBaru456', $owner->password))->toBeTrue();

    expect(User::query()->where('email', 'owner@putramandiri.test')->count())->toBe(1)
        ->and(User::query()->where('email', 'hardiansah@putramandiri.test')->count())->toBe(1)
        ->and(Category::query()->count())->toBe(6);
});
