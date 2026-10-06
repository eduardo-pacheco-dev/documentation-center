<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected from the root to the admin panel', function () {
    $this->get('/')->assertRedirect('/admin');
});

test('guests are redirected from the admin panel to the login page', function () {
    $this->get('/admin')->assertRedirect('/login');
});
