<?php

use Illuminate\Support\Facades\Route;

Route::get('/reset-password/{token}', function ($token) {
    return redirect(
        config('app.frontend_url', 'https://savethestory.kz') .
        '/reset-password?token=' . $token .
        '&email=' . request('email')
    );
})->name('password.reset');