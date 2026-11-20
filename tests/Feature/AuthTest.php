<?php

test('owner dan kasir dapat login', function (string $role) {
    $user = $role === 'owner'
        ? pmOwner(['email' => 'owner@example.test', 'password' => 'rahasia123'])
        : pmKasir(['email' => 'kasir@example.test', 'password' => 'rahasia123']);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'rahasia123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
})->with(['owner', 'kasir']);

test('password salah ditolak dengan pesan Bahasa Indonesia', function () {
    $user = pmKasir(['email' => 'kasir@example.test', 'password' => 'rahasia123']);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password-salah',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['email' => 'Email atau password salah.']);
    $this->assertGuest();
});

test('tamu tidak dapat mengakses halaman internal', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with([
    'dashboard',
    'pos.index',
    'sales.index',
    'digital.index',
    'categories.index',
    'products.index',
    'reports.sales',
    'expenses.index',
    'users.index',
]);

test('pengguna dapat logout', function () {
    $user = pmKasir();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});
