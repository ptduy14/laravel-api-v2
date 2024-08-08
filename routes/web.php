<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/activate/{token}', [AuthController::class, 'activeUser'])->name('auth.active');