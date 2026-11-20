<?php

test('guest diarahkan ke halaman login', function () {
    $this->get('/')->assertRedirect(route('dashboard'));
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});
