<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('example::index'))->name('index');
